<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'manage stages',
            'manage groups',
            'manage subjects',
            'manage students',
            'manage teachers',
            'manage lectures',
            'scan attendance',
            'override attendance',
            'view reports',
            'print qrs',
            'manage settings',
            'view activity log',
            'access archive'
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Reset cached roles and permissions AGAIN after creation
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles and assign created permissions
        $roleTeacher = Role::findOrCreate('teacher', 'web');
        $roleTeacher->givePermissionTo([
            'scan attendance',
            'override attendance', // Locked to 24 hours via UI/Policy
            'view reports'
        ]);

        $roleAdmin = Role::findOrCreate('admin', 'web');
        $roleAdmin->givePermissionTo([
            'manage stages',
            'manage groups',
            'manage subjects',
            'manage students',
            'manage lectures',
            'override attendance',
            'view reports',
            'print qrs',
            'access archive'
        ]);

        $roleSuperAdmin = Role::findOrCreate('super_admin', 'web');
        $roleSuperAdmin->givePermissionTo(Permission::all());

        // Create a default Super Admin (safe to run multiple times)
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'full_name' => 'Super Admin',
                'password'  => bcrypt('password'),
            ]
        );
        $superAdmin->syncRoles('super_admin');
    }
}

