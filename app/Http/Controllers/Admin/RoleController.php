<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()->withCount(['permissions', 'users'])->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');
        $permissionGroups = Permission::query()->orderBy('name')->get()->groupBy(
            fn (Permission $permission) => str($permission->name)->before('.')->toString()
        );

        return view('admin.roles.edit', compact('role', 'permissionGroups'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->name === 'ADMINISTRADOR', 403, 'El rol administrador está protegido.');

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);

        DB::transaction(fn () => $role->syncPermissions($validated['permissions'] ?? []));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', "Permisos del rol {$role->name} actualizados.");
    }
}
