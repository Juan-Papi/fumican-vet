<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use App\Services\Users\RoleService;
use App\Services\Users\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    public function __construct(protected UserService $service, protected RoleService $roleService) {}

    public function index(): InertiaResponse
    {
        return Inertia::render('Users/Index', [
            'users' => $this->service->getAllPaginated(),
            'roles' => $this->roleService->getAll(),
            'filters' => [],
        ]);
    }

    public function search(Request $request): InertiaResponse
    {
        $filters = $request->only('search_term');
        return Inertia::render('Users/Index', [
            'users' => $this->service->search($filters),
            'roles' => $this->roleService->getAll(),
            'filters' => $filters,
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $user = $this->service->create($data);
            $user->roles()->attach($data['role_id']);
            DB::commit();
            return response()->json(['message' => 'Usuario creado correctamente.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el usuario: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $this->service->update($user->id, $data);
            if (isset($data['role_id'])) {
                $user->roles()->sync($data['role_id']);
            }
            DB::commit();
            return response()->json(['message' => 'Usuario actualizado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar el usuario: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(User $user): JsonResponse
    {
        if (Auth::user()->id == $user->id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario.'], 403);
        }

        DB::beginTransaction();
        try {
            $this->service->delete($user->id);
            DB::commit();
            return response()->json(['message' => 'Usuario eliminado correctamente.']);
        } catch (QueryException $e) {
            DB::rollBack();
            // Código '23000' es el estándar SQL para violación de integridad (foreign key)
            if ($e->getCode() == '23000') {
                return response()->json(['message' => 'No se puede eliminar: el usuario tiene registros asociados.'], 409); // 409 Conflict
            }
            return response()->json(['message' => 'Error de base de datos al eliminar.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Ocurrió un error inesperado al eliminar.'], 500);
        }
    }
}
