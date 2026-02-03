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
        $permissions = Permission::all();
        return view('admin.permissions.form', ['role'=>new Role(),'permissions'=>$permissions]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:roles,slug',
            'permissions' => 'array'
        ]);
        $role = Role::create(['name'=>$data['name'],'slug'=>$data['slug']]);
        $role->permissions()->sync($data['permissions'] ?? []);
        return response()->json(['redirect'=>route('admin.permissions.index')]);
    }

    public function edit(Role $permission)
    {
        $permissions = Permission::all();
        return view('admin.permissions.form', ['role'=>$permission,'permissions'=>$permissions]);
    }

    public function update(Request $request, Role $permission)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:roles,slug,'.$permission->id,
            'permissions' => 'array'
        ]);
        $permission->update(['name'=>$data['name'],'slug'=>$data['slug']]);
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