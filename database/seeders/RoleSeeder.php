<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'deposit money',
            'view balance',
            'view transaction history',
            'search transactions',
            'change password',
            'edit profile',

            'create customer account',
            'approve large transactions',
            'view customer info',
            'freeze account',
            'unfreeze account',

            'view statistics dashboard',
            'manage users',
            'manage employees',
            'manage permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'api']);
        }


        $userRole = Role::create(['name' => 'user', 'guard_name' => 'api']);
        $userRole->givePermissionTo([
            'deposit money',
            'view balance',
            'view transaction history',
            'search transactions',
            'change password',
            'edit profile',
        ]);

        $employeeRole = Role::create(['name' => 'employee', 'guard_name' => 'api']);
        $employeeRole->givePermissionTo([
            'deposit money',
            'view balance',
            'view transaction history',
            'search transactions',
            'change password',
            'edit profile',
            'create customer account',
            'approve large transactions',
            'view customer info',
            'freeze account',
            'unfreeze account',
        ]);

        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo(Permission::all());
    }
}
