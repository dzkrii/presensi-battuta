<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_attendance","view_any_attendance","create_attendance","update_attendance","restore_attendance","restore_any_attendance","replicate_attendance","reorder_attendance","delete_attendance","delete_any_attendance","force_delete_attendance","force_delete_any_attendance","view_office","view_any_office","create_office","update_office","restore_office","restore_any_office","replicate_office","reorder_office","delete_office","delete_any_office","force_delete_office","force_delete_any_office","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_schedule","view_any_schedule","create_schedule","update_schedule","restore_schedule","restore_any_schedule","replicate_schedule","reorder_schedule","delete_schedule","delete_any_schedule","force_delete_schedule","force_delete_any_schedule","view_shift","view_any_shift","create_shift","update_shift","restore_shift","restore_any_shift","replicate_shift","reorder_shift","delete_shift","delete_any_shift","force_delete_shift","force_delete_any_shift","view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","view_leave","view_any_leave","create_leave","update_leave","restore_leave","restore_any_leave","replicate_leave","reorder_leave","delete_leave","delete_any_leave","force_delete_leave","force_delete_any_leave"]},{"name":"Pegawai","guard_name":"web","permissions":["view_attendance","view_any_attendance","create_attendance","view_schedule","view_any_schedule","view_leave","view_any_leave"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
