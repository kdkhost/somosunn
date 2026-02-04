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
            'bio' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            
            // Endereço
            'cep' => 'nullable|string|max:9',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            
            // Redes Sociais
            'website' => 'nullable|url|max:255',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            
            // Privacidade
            'show_email_public' => 'nullable|boolean',
            'show_phone_public' => 'nullable|boolean',
            'show_address_public' => 'nullable|boolean',
        ]);

        // Converte checkboxes para boolean
        $data['show_email_public'] = $request->has('show_email_public');
        $data['show_phone_public'] = $request->has('show_phone_public');
        $data['show_address_public'] = $request->has('show_address_public');

        // Upload de foto
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            \Log::info('Upload de foto iniciado para user ' . $user->id);
            
            // Remove foto antiga
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
                \Log::info('Foto antiga removida: ' . $user->photo);
            }
            
            // Salva nova foto
            $file = $request->file('photo');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/avatars', $filename, 'public');
            $data['photo'] = $path;
            
            \Log::info('Nova foto salva: ' . $path);
        } else {
            \Log::info('Nenhum arquivo de foto válido enviado');
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->fill($data);
        $user->save();
        
        \Log::info('User atualizado. Photo no banco: ' . $user->photo);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso!',
                'photo_url' => $user->photo ? asset('storage/'.$user->photo) : null,
                'debug' => [
                    'photo_path' => $user->photo,
                    'full_url' => $user->photo ? asset('storage/'.$user->photo) : null
                ]
            ]);
        }

        return redirect()->route('admin.profile.edit')->with('success', 'Perfil atualizado com sucesso!');
    }
}
