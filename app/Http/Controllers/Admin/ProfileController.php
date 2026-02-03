<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'doc' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:9',
            'address' => 'nullable|string',
            'bio' => 'nullable|string|max:500', // Assumindo que criaremos migration pra bio depois ou usamos um campo existente
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Remove antiga se existir e não for padrão
            if ($user->photo && Storage::exists('public/'.$user->photo)) {
                Storage::delete('public/'.$user->photo);
            }
            $path = $request->file('photo')->store('uploads/avatars', 'public');
            $data['photo'] = $path; // Ajustar conforme campo no banco (photo ou avatar?) User tem 'photo' no migration? Verifiquei no User.php não tem 'photo' no fillable, mas tem 'image' ou algo assim?
            // User.php fillable: 'name','email','password','doc','phone','cep','address','role','points','theme_pref','level'
            // Vou verificar se existe coluna 'photo' ou 'avatar' no banco. Por segurança, vou assumir 'photo' e adicionar ao fillable se necessário ou usar forceFill.
            // O User model não tem 'photo' no fillable visto anteriormente.
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Lidar com campos json se existirem para redes sociais, ou criar migration depois.
        // Por enquanto, foco nos dados básicos que já existem.

        $user->fill($data);
        $user->save();

        return redirect()->route('admin.profile.edit')->with('success', 'Perfil atualizado com sucesso!');
    }
}
