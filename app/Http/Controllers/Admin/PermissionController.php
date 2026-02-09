<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->paginate(20);
        $permissions = Permission::all();
        return view('admin.permissions.index', compact('roles','permissions'));
    }

    public function create()
    {
        // Compatibilidade: verifica se a coluna category existe
        $hasCategory = \Schema::hasColumn('permissions', 'category');
        
        if ($hasCategory) {
            $permissionsGrouped = Permission::grouped();
        } else {
            // Fallback: agrupa por prefixo do nome
            $permissionsGrouped = Permission::orderBy('name')->get()->groupBy(function ($p) {
                return explode('.', $p->name)[0] ?? 'outros';
            });
        }
        
        return view('admin.permissions.form', [
            'role' => new Role(),
            'permissionsGrouped' => $permissionsGrouped,
            'hasCategory' => $hasCategory
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'label' => 'nullable|string|max:100',
            'permissions' => 'array'
        ]);
        $role = Role::create([
            'name' => $data['name'],
            'label' => $data['label'] ?? $data['name'],
        ]);
        $role->permissions()->sync($data['permissions'] ?? []);
        return response()->json(['redirect'=>route('admin.permissions.index')]);
    }

    public function edit(Role $permission)
    {
        // Compatibilidade: verifica se a coluna category existe
        $hasCategory = \Schema::hasColumn('permissions', 'category');
        
        if ($hasCategory) {
            $permissionsGrouped = Permission::grouped();
        } else {
            // Fallback: agrupa por prefixo do nome
            $permissionsGrouped = Permission::orderBy('name')->get()->groupBy(function ($p) {
                return explode('.', $p->name)[0] ?? 'outros';
            });
        }
        
        return view('admin.permissions.form', [
            'role' => $permission,
            'permissionsGrouped' => $permissionsGrouped,
            'hasCategory' => $hasCategory
        ]);
    }

    public function update(Request $request, Role $permission)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,'.$permission->id,
            'label' => 'nullable|string|max:100',
            'permissions' => 'array'
        ]);
        $permission->update([
            'name' => $data['name'],
            'label' => $data['label'] ?? $data['name'],
        ]);
        $permission->permissions()->sync($data['permissions'] ?? []);
        return response()->json(['redirect'=>route('admin.permissions.index')]);
    }

    public function destroy(Role $permission)
    {
        $permission->permissions()->detach();
        $permission->delete();
        return response()->json(['ok'=>true]);
    }
}