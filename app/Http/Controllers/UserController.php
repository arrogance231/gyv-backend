<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * List system users with their roles.
     */
    public function index()
    {
        if (Gate::denies('manage-users')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return User::with('roles')->get();
    }

    /**
     * Create a user and attach a role.
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
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $role = Role::where('slug', $validated['role'])->first();
        $user->roles()->attach($role);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('roles')
        ], 201);
    }

    /**
     * Update user details and sync roles.
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

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles')
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
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
