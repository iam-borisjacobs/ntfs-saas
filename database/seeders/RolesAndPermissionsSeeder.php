<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'file.create',
            'file.dispatch',
            'file.receive',
            'file.archive',
            'file.override_status',
            'audit.view',
            'audit.export',
            'user.manage',
            'user.reset_password',
            'department.manage'
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::findOrCreate($permission);
        }

        $registryOfficer = \Spatie\Permission\Models\Role::findOrCreate('Registry Officer');
        $registryOfficer->syncPermissions(['file.create', 'file.dispatch', 'file.receive']);

        $deptOfficer = \Spatie\Permission\Models\Role::findOrCreate('Dept. Officer');
        $deptOfficer->syncPermissions(['file.dispatch', 'file.receive']);

        $director = \Spatie\Permission\Models\Role::findOrCreate('Director');
        $director->syncPermissions(['file.dispatch', 'file.receive', 'file.archive', 'audit.view']);

        $auditor = \Spatie\Permission\Models\Role::findOrCreate('Auditor');
        $auditor->syncPermissions(['audit.view', 'audit.export']);

        $sysAdmin = \Spatie\Permission\Models\Role::findOrCreate('Sys Admin');
        $sysAdmin->syncPermissions(['audit.view', 'user.manage', 'user.reset_password', 'department.manage']);
    }
}
