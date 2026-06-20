<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Rules\ValidEmailAddress;
use App\Services\PointsService;
use App\Services\WatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('panel.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $wasProfileComplete = method_exists($user, 'isProfileComplete') ? $user->isProfileComplete() : false;
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', new ValidEmailAddress(), 'unique:users,email,' . $user->id],
            'password' => 'nullable|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'doc' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'birth_date' => 'nullable|date|before:today',
            'occupation' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:100',

            // New fields for Segment/Interests
            'segment_select' => 'nullable|string',
            'segment_custom' => 'nullable|string|max:120',
            'interests_list' => 'nullable|array',
            'interests_list.*' => 'string',
            'interests_custom' => 'nullable|string',

            'bio' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'cover_photo' => 'nullable|image|max:4096',

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

            'pix_key' => 'nullable|string|max:255',

            'theme_pref' => 'nullable|string|in:light,dark',
        ]);

        if (!$user->canManageReceivingPixKey()) {
            unset($data['pix_key']);
        }

        $data['show_email_public'] = $request->has('show_email_public');
        $data['show_phone_public'] = $request->has('show_phone_public');
        $data['show_address_public'] = $request->has('show_address_public');
        $data['hide_profile'] = $request->has('hide_profile');

        // 1. Process Segment
        if ($request->segment_select === 'Outros') {
            $data['segment'] = $request->segment_custom;
        } else {
            $data['segment'] = $request->segment_select;
        }

        // Limpar máscara do documento (remover tudo que não for número)
        if (isset($data['doc'])) {
            $data['doc'] = preg_replace('/\D/', '', $data['doc']);
        }

        // 2. Process Interests
        $interests = $request->interests_list ?? [];
        if (!empty($request->interests_custom)) {
            // Split custom interests by comma and merge
            $customInterests = array_map('trim', explode(',', $request->interests_custom));
            $interests = array_merge($interests, $customInterests);
        }
        // Remove duplicates and empty values
        $interests = array_unique(array_filter($interests, function ($value) {
            return !empty(trim($value));
        }));

        $data['interests'] = implode(', ', $interests);

        // Upload avatar
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            if ($user->photo && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
            }

            $file = $request->file('photo');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = app(WatermarkService::class)->processPublicImage(
                $file,
                'uploads/imagens/avatars',
                $filename,
                ['prefix' => 'avatar-' . $user->id]
            );

            if (file_exists(public_path($path))) {
                $data['photo'] = $path;
            }
        }

        // Upload capa
        if ($request->hasFile('cover_photo') && $request->file('cover_photo')->isValid()) {
            if ($user->cover_photo && file_exists(public_path($user->cover_photo))) {
                unlink(public_path($user->cover_photo));
            }

            $file = $request->file('cover_photo');
            $filename = 'cover_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = app(WatermarkService::class)->processPublicImage(
                $file,
                'uploads/imagens/covers',
                $filename,
                ['prefix' => 'cover-' . $user->id]
            );

            if (file_exists(public_path($path))) {
                $data['cover_photo'] = $path;
            }
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $emailChanged = mb_strtolower((string) $user->email) !== $data['email'];

        $user->fill($data);
        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null]);
        }
        $saved = $user->save();
        $user->refresh();

        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                \Log::error('Falha ao enviar verificação do novo e-mail: ' . $e->getMessage());
            }
        }

        // Gamificação: pontuar completar perfil (PointsService bloqueia repetição automaticamente)
        try {
            $isProfileComplete = method_exists($user, 'isProfileComplete') ? $user->isProfileComplete() : false;
            if ($isProfileComplete && !$wasProfileComplete) {
                (new PointsService())->award($user, 'complete_profile');
            }
        } catch (\Throwable $e) {
            \Log::warning('Falha ao pontuar completar perfil: ' . $e->getMessage());
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $emailChanged
                    ? 'Perfil atualizado. Valide o novo e-mail antes de realizar compras.'
                    : 'Perfil atualizado com sucesso!',
                'photo_url' => $user->photo ? asset($user->photo) : null,
                'cover_url' => $user->cover_photo ? asset($user->cover_photo) : null,
                'debug' => [
                    'photo_path' => $user->photo,
                    'cover_path' => $user->cover_photo,
                    'full_url' => $user->photo ? asset($user->photo) : null,
                    'file_exists' => $user->photo ? file_exists(public_path($user->photo)) : false,
                    'save_result' => $saved,
                ],
            ]);
        }

        return redirect()->route('panel.profile.edit')->with(
            'success',
            $emailChanged ? 'Perfil atualizado. Valide o novo e-mail antes de realizar compras.' : 'Perfil atualizado com sucesso!'
        );
    }

    /**
     * Upload de foto de perfil ou capa via AJAX (com crop no frontend).
     * Recebe a imagem ja cortada como base64 ou file.
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'type' => 'required|in:photo,cover_photo',
            'image' => 'required',
        ]);

        $user = auth()->user();
        $type = $request->input('type');

        // Aceita base64 (do cropper) ou file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
        } elseif (is_string($request->input('image')) && str_starts_with($request->input('image'), 'data:image')) {
            // Decode base64
            $base64 = $request->input('image');
            $data = explode(',', $base64, 2);
            $decoded = base64_decode($data[1] ?? '');
            if (!$decoded) {
                return response()->json(['success' => false, 'error' => 'Imagem invalida.'], 422);
            }
            // Detectar extensao
            $mime = explode(';', explode(':', $data[0] ?? '')[1] ?? '')[0] ?? 'image/jpeg';
            $ext = match ($mime) {
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                default => 'jpg',
            };
            $tmpPath = tempnam(sys_get_temp_dir(), 'crop_') . '.' . $ext;
            file_put_contents($tmpPath, $decoded);
            $file = new \Illuminate\Http\UploadedFile($tmpPath, 'cropped.' . $ext, $mime, null, true);
        } else {
            return response()->json(['success' => false, 'error' => 'Nenhuma imagem recebida.'], 422);
        }

        if (!$file->isValid()) {
            return response()->json(['success' => false, 'error' => 'Arquivo invalido.'], 422);
        }

        $isAvatar = $type === 'photo';
        $folder = $isAvatar ? 'uploads/imagens/avatars' : 'uploads/imagens/covers';
        $prefix = $isAvatar ? 'avatar' : 'cover';
        $oldPath = $isAvatar ? $user->photo : $user->cover_photo;

        // Remover arquivo antigo
        if ($oldPath && file_exists(public_path($oldPath))) {
            @unlink(public_path($oldPath));
        }

        $filename = $prefix . '_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = app(WatermarkService::class)->processPublicImage(
            $file,
            $folder,
            $filename,
            ['prefix' => $prefix . '-' . $user->id]
        );

        if (!file_exists(public_path($path))) {
            return response()->json(['success' => false, 'error' => 'Falha ao salvar imagem.'], 500);
        }

        $user->update([$type => $path]);

        return response()->json([
            'success' => true,
            'url' => asset($path) . '?t=' . time(),
            'message' => ($isAvatar ? 'Foto de perfil' : 'Capa') . ' atualizada!',
        ]);
    }
}

