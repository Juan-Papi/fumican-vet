<?php

namespace App\Http\Middleware;

use App\Models\Users\Menu;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $permissions = $request->user()
            ? $request->user()->getAllPermissions()
            : null;
        $permissionsIds = $permissions ? $permissions->pluck('id') : null;
        return array_merge(parent::share($request), [
            'auth.user_permissions' => $permissions,
            'auth.user_menus' => $request->user()
                ? Menu::whereNull('parent_id')->with(['submenus' => function ($query) use ($permissionsIds) {
                    $query->whereIn('permission_id', $permissionsIds);
                }])->get()
                : null,
        ]);
    }
}
