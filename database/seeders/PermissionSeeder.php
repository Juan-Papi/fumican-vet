<?php

namespace Database\Seeders;

use App\Models\Users\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::create(['name' => 'listar usuarios']);
        Permission::create(['name' => 'crear usuarios']);
        Permission::create(['name' => 'editar usuarios']);
        Permission::create(['name' => 'ver usuarios']);
        Permission::create(['name' => 'habilitar usuarios']);
        Permission::create(['name' => 'deshabilitar usuarios']);

        $permissions = [
            'listar roles', 'crear roles', 'editar roles', 'ver roles', 'eliminar roles',
            'listar permisos', 'crear permisos', 'editar permisos', 'ver permisos', 'eliminar permisos',
            'listar clientes', 'crear clientes', 'editar clientes', 'ver clientes', 'eliminar clientes',
            'listar consultas medicas', 'crear consultas medicas', 'editar consultas medicas', 'ver consultas medicas', 'eliminar consultas medicas',
            'listar mascotas', 'crear mascotas', 'editar mascotas', 'ver mascotas', 'eliminar mascotas',
            'listar vacunas', 'crear vacunas', 'editar vacunas', 'ver vacunas', 'eliminar vacunas',
            'listar notas de ventas', 'crear notas de ventas', 'editar notas de ventas', 'ver notas de ventas', 'eliminar notas de ventas',
            'listar notas de compras', 'crear notas de compras', 'editar notas de compras', 'ver notas de compras', 'eliminar notas de compras',
            'listar almacenes', 'crear almacenes', 'editar almacenes', 'ver almacenes', 'eliminar almacenes',
            'listar medicamentos', 'crear medicamentos', 'editar medicamentos', 'ver medicamentos', 'eliminar medicamentos',
            'listar categorias', 'crear categorias', 'editar categorias', 'ver categorias', 'eliminar categorias',
            'listar proveedores', 'crear proveedores', 'editar proveedores', 'ver proveedores', 'eliminar proveedores',
            'listar inventario', 'crear inventario', 'editar inventario', 'ver inventario', 'eliminar inventario',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
