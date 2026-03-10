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
            Permission::create(['name' => $permission]);
        }

        // create roles and assign created permissions
        $roleTeacher = Role::create(['name' => 'teacher']);
        $roleTeacher->givePermissionTo([
            'scan attendance',
            'override attendance', // Locked to 24 hours via UI/Policy
            'view reports'
        ]);

        $roleAdmin = Role::create(['name' => 'admin']);
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

        $roleSuperAdmin = Role::create(['name' => 'super_admin']);
        $roleSuperAdmin->givePermissionTo(Permission::all());

        // Create a default Super Admin
        $superAdmin = User::create([
            'full_name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole('super_admin');
    }
}

