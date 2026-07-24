<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $permission = Permission::updateOrCreate(
                ['slug' => 'publish-content'],
                ['name' => 'Publish page changes and drafts']
            );

            // Sync with admin role
            $adminRole = Role::where('slug', 'admin')->first();
            if ($adminRole) {
                $adminRole->permissions()->syncWithoutDetaching([$permission->id]);
            }
        } catch (\Throwable $e) {
            // Safe fallback
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            $permission = Permission::where('slug', 'publish-content')->first();
            if ($permission) {
                $adminRole = Role::where('slug', 'admin')->first();
                if ($adminRole) {
                    $adminRole->permissions()->detach($permission->id);
                }
                $permission->delete();
            }
        } catch (\Throwable $e) {
            // Safe fallback
        }
    }
};
