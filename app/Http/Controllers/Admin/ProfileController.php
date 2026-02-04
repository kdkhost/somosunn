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
            
            // Privacidade - SEM validação boolean (checkboxes não enviam quando desmarcados)
        ]);

        // Converte checkboxes para boolean
        $data['show_email_public'] = $request->has('show_email_public');
        $data['show_phone_public'] = $request->has('show_phone_public');
        $data['show_address_public'] = $request->has('show_address_public');

        // Upload de foto
        $uploadedPhotoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            \Log::info('Upload de foto iniciado para user ' . $user->id);
            
            // Remove foto antiga
            if ($user->photo && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
                \Log::info('Foto antiga removida: ' . $user->photo);
            }
            
            // Salva nova foto DIRETAMENTE em public/uploads/imagens/avatars
            $file = $request->file('photo');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Garante que o diretório existe
            $directory = public_path('uploads/imagens/avatars');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Move o arquivo diretamente para public/uploads/imagens/avatars
            $path = 'uploads/imagens/avatars/' . $filename;
            $file->move($directory, $filename);
            
            // Verifica se o arquivo foi realmente salvo
            if (file_exists(public_path($path))) {
                $uploadedPhotoPath = $path;
                $data['photo'] = $path;
                \Log::info('Nova foto salva com sucesso em: ' . $path);
            } else {
                \Log::error('Falha ao salvar arquivo de foto');
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao salvar arquivo da foto. Tente novamente.'
                    ], 500);
                }
            }
        } else {
            \Log::info('Nenhum arquivo de foto válido enviado');
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Salva os dados
        $user->fill($data);
        $saved = $user->save();
        
        // Força refresh do modelo para pegar dados do banco
        $user->refresh();
        
        \Log::info('User save result: ' . ($saved ? 'true' : 'false'));
        \Log::info('Photo no banco após save: ' . ($user->photo ?? 'NULL'));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso!',
                'photo_url' => $user->photo ? asset($user->photo) : null,
                'debug' => [
                    'photo_path' => $user->photo,
                    'full_url' => $user->photo ? asset($user->photo) : null,
                    'file_exists' => $user->photo ? file_exists(public_path($user->photo)) : false,
                    'save_result' => $saved
                ]
            ]);
        }

        return redirect()->route('admin.profile.edit')->with('success', 'Perfil atualizado com sucesso!');
    }
}
