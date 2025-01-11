<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'name' => 'Dashboard',
                'icon' => 'fa-solid fa-house',
                'link' => '/dashboard',
                'parent_id' => null,
            ],
            [
                'name' => 'Usuarios',
                'icon' => 'fa-solid fa-users',
                'link' => null,
                'parent_id' => null,
            ],
            [
                'name' => 'Servicios',
                'icon' => 'fa-solid fa-briefcase',
                'link' => null,
                'parent_id' => null,
            ],
            [
                'name' => 'Ventas',
                'icon' => 'fa-solid fa-money-bill',
                'link' => null,
                'parent_id' => null,
            ],
        ]);
        DB::table('menus')->insert([
            [
                'name' => 'Personal',
                'icon' => null,
                'link' => '/users/users',
                'permission_id' => DB::table('permissions')->where('name', 'listar usuarios')->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Usuarios')->first()->id,
            ],
            [
                'name' => 'Roles',
                'icon' => null,
                'link' => '/users/roles',
                'permission_id' => DB::table('permissions')->where('name', 'listar roles')->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Usuarios')->first()->id,
            ],
            // [
            //     'name' => 'Permisos',
            //     'icon' => null,
            //     'link' => '/users/permissions',
            //     'permission_id' => DB::table('permissions')->where('name', 'listar permisos')->first()->id,
            //     'parent_id' => DB::table('menus')->where('name', 'Usuarios')->first()->id,
            // ],
            // MODULO SERVICIOS
            [
                'name' => 'Clientes',
                'icon' => null,
                'link' => '/services/customers',
                'permission_id' => DB::table('permissions')->where('name', 'listar clientes')->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Servicios')->first()->id,
            ],
            [
                'name' => 'Consultas médicas',
                'icon' => null,
                'link' => '/services/medical-consultations',
                'permission_id' => DB::table('permissions')->where('name', 'listar consultas medicas')->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Servicios')->first()->id,
            ],
            [
                'name' => 'Mascotas',
                'icon' => null,
                'link' => '/services/pets',
                'permission_id' => DB::table('permissions')->where('name', 'listar mascotas')->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Servicios')->first()->id,
            ],
            // MODULO DE VENTAS
            [
                'name' => 'Notas de ventas',
                'icon' => null,
                'link' => '/sales/sales-note',
                'permission_id' => DB::table('permissions')->where('name', PermissionEnum::LISTAR_NOTAS_DE_VENTAS->value)->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Ventas')->first()->id,
            ],
            [
                'name' => 'Notas de compras',
                'icon' => null,
                'link' => '/sales/purchases',
                'permission_id' => DB::table('permissions')->where('name', PermissionEnum::LISTAR_NOTAS_DE_COMPRAS->value)->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Ventas')->first()->id,
            ],
            [
                'name' => 'Almacen',
                'icon' => null,
                'link' => '/sales/warehouses',
                'permission_id' => DB::table('permissions')->where('name', 'listar almacenes')->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Ventas')->first()->id,
            ],
            [
                'name' => 'Medicamentos',
                'icon' => null,
                'link' => '/sales/medicaments',
                'permission_id' => DB::table('permissions')->where('name', 'listar medicamentos')->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Ventas')->first()->id,
            ],
            [
                'name' => 'Categorias',
                'icon' => null,
                'link' => '/sales/categories',
                'permission_id' => DB::table('permissions')->where('name', PermissionEnum::LISTAR_CATEGORIAS->value)->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Ventas')->first()->id,
            ],
            [
                'name' => 'Proveedores',
                'icon' => null,
                'link' => '/sales/suppliers',
                'permission_id' => DB::table('permissions')->where('name', 'listar proveedores')->first()->id,
                'parent_id' => DB::table('menus')->where('name', 'Ventas')->first()->id,
            ],
        ]);
    }
}
