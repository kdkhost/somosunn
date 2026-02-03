<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at','desc')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $user = new User();
        return view('admin.users.form', compact('user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'nullable|string',
            'level' => 'nullable|string',
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('admin.users.index')->with('success','Usuário criado.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6',
            'role' => 'nullable|string',
            'level' => 'nullable|string',
        ]);
        if(!empty($data['password'])){
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return redirect()->route('admin.users.index')->with('success','Usuário atualizado.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['ok'=>true]);
    }
}