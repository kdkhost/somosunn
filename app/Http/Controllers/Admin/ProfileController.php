<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointsLog;
use App\Rules\ValidEmailAddress;
use App\Services\WatermarkService;
use App\Services\PointsService;
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
        $wasProfileComplete = method_exists($user, 'isProfileComplete') ? $user->isProfileComplete() : false;
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', new ValidEmailAddress(), 'unique:users,email,' . $user->id],
            'password' => 'nullable|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'doc' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:100',
            'segment' => 'nullable|string|max:120',
            'interests' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'cover_photo' => 'nullable|image|max:4096', // Capa pode ser maior (4MB)

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

            // Privacidade - SEM validação boolean (checkboxes não enviam quando desmarcados)
        ]);

        if (!$user->canManageReceivingPixKey()) {
            unset($data['pix_key']);
        }

        // Converte checkboxes para boolean
        $data['show_email_public'] = $request->has('show_email_public');
        $data['show_phone_public'] = $request->has('show_phone_public');
        $data['show_address_public'] = $request->has('show_address_public');
        $data['hide_profile'] = $request->has('hide_profile');

        // Upload de foto de perfil
        $uploadedPhotoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            \Log::info('Upload de foto iniciado para user ' . $user->id);

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
                $uploadedPhotoPath = $path;
                $data['photo'] = $path;
            }
        }

        // Upload de foto de capa
        if ($request->hasFile('cover_photo') && $request->file('cover_photo')->isValid()) {
            \Log::info('Upload de capa iniciado para user ' . $user->id);

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

        // Salva os dados
        $user->fill($data);
        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null]);
        }
        $saved = $user->save();

        // Força refresh do modelo
        $user->refresh();

        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                \Log::error('Falha ao enviar verificação do novo e-mail: ' . $e->getMessage());
            }
        }

        // Gamificação: pontuar completar perfil (apenas 1x)
        try {
            $isProfileComplete = method_exists($user, 'isProfileComplete') ? $user->isProfileComplete() : false;
            if ($isProfileComplete && !$wasProfileComplete) {
                $alreadyAwarded = PointsLog::query()
                    ->where('user_id', $user->id)
                    ->where('action_key', 'complete_profile')
                    ->exists();

                if (!$alreadyAwarded) {
                    (new PointsService())->award($user, 'complete_profile');
                }
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
                    'save_result' => $saved
                ]
            ]);
        }

        return redirect()->route('admin.profile.edit')->with(
            'success',
            $emailChanged ? 'Perfil atualizado. Valide o novo e-mail antes de realizar compras.' : 'Perfil atualizado com sucesso!'
        );
    }
}
