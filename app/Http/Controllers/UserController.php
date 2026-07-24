<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * List system users with their roles and permission overrides.
     */
    public function index()
    {
        if (Gate::denies('manage-users')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return User::with(['roles', 'permissions'])->get();
    }

    /**
     * List all system permissions.
     */
    public function permissions()
    {
        if (Gate::denies('manage-users')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return Permission::orderBy('name', 'asc')->get();
    }

    /**
     * Create a user, attach role, and optionally sync permission overrides.
     */
    public function store(Request $request)
    {
        if (Gate::denies('manage-users')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,slug',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,slug'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $role = Role::where('slug', $validated['role'])->first();
        $user->roles()->attach($role);

        if (!empty($validated['permissions'])) {
            $permissionIds = Permission::whereIn('slug', $validated['permissions'])->pluck('id');
            $user->permissions()->sync($permissionIds);
        }

        ActivityLogger::log('created', 'User', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role->slug
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load(['roles', 'permissions'])
        ], 201);
    }

    /**
     * Update user details, sync roles, and sync permission overrides.
     */
    public function update(Request $request, $id)
    {
        if (Gate::denies('manage-users')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|exists:roles,slug',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,slug'
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        $role = Role::where('slug', $validated['role'])->first();
        $user->roles()->sync([$role->id]);

        $permissionIds = [];
        if (isset($validated['permissions'])) {
            $permissionIds = Permission::whereIn('slug', $validated['permissions'])->pluck('id')->toArray();
        }
        $user->permissions()->sync($permissionIds);

        ActivityLogger::log('updated', 'User', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role->slug,
            'permissions_count' => count($permissionIds)
        ]);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load(['roles', 'permissions'])
        ]);
    }

    /**
     * Delete user, avoiding self-deletion.
     */
    public function destroy($id, Request $request)
    {
        if (Gate::denies('manage-users')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if ($request->user()->id == $id) {
            return response()->json(['message' => 'Cannot delete your own user account.'], 400);
        }

        $user = User::findOrFail($id);
        $userEmail = $user->email;
        $user->delete();

        ActivityLogger::log('deleted', 'User', $id, [
            'email' => $userEmail
        ]);

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
