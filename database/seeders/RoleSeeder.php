<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Users\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (RoleEnum::values() as $roleName) {
            Role::create(['name' => $roleName]);
        }

        $roleGerentePropietario = Role::where('name', RoleEnum::GERENTE_PROPIETARIO->value)->first();

        $allPermissions = Permission::all();
        $roleGerentePropietario->syncPermissions($allPermissions);

        $roleSecretaria = Role::where('name', RoleEnum::SECRETARIO->value)->first();
        $roleSecretaria->givePermissionTo(PermissionEnum::LISTAR_CLIENTES->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::CREAR_CLIENTES->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::EDITAR_CLIENTES->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::ELIMINAR_CLIENTES->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::VER_CLIENTES->value);

        $roleSecretaria->givePermissionTo(PermissionEnum::LISTAR_MASCOTAS->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::CREAR_MASCOTAS->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::EDITAR_MASCOTAS->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::ELIMINAR_MASCOTAS->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::VER_MASCOTAS->value);

        $roleSecretaria->givePermissionTo(PermissionEnum::LISTAR_ALMACENES->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::CREAR_ALMACENES->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::EDITAR_ALMACENES->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::ELIMINAR_ALMACENES->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::VER_ALMACENES->value);

        $roleSecretaria->givePermissionTo(PermissionEnum::LISTAR_MEDICAMENTOS->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::CREAR_MEDICAMENTOS->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::EDITAR_MEDICAMENTOS->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::ELIMINAR_MEDICAMENTOS->value);
        $roleSecretaria->givePermissionTo(PermissionEnum::VER_MEDICAMENTOS->value);
    }
}
