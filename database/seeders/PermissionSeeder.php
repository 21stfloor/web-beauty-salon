<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'edit articles']);
        Permission::create(['name' => 'delete articles']);
        Permission::create(['name' => 'publish articles']);
        Permission::create(['name' => 'unpublish articles']);

        // create roles and assign created permissions

        // this can be done as separate statements
        $role = Role::create(['name' => 'patient']);
        $role->givePermissionTo('edit articles');

        // or may be done by chaining
        $role = Role::create(['name' => 'doctor'])
            ->givePermissionTo(['publish articles', 'unpublish articles']);

        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());
    }
}