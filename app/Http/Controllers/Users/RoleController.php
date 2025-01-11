<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreRoleRequest;
use App\Http\Requests\Users\UpdateRoleRequest;
use App\Services\Users\PermissionService;
use App\Services\Users\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function __construct(protected RoleService $roleService, protected PermissionService $permissionService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = $this->roleService->getAllPaginated();
        $permissions = $this->permissionService->getAll();
        return Inertia::render('Users/Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $role = $this->roleService->create($data);
            $permissionsIds = $data['permissions'];
            $role->permissions()->sync($permissionsIds);
            DB::commit();
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while creating the role.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $this->roleService->update($id, $data);
            $permissionsIds = $data['permissions'];
            $role = $this->roleService->getById($id);
            $role->permissions()->sync($permissionsIds);
            DB::commit();
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while updating the role.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
