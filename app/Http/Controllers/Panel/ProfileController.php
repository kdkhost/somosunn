<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
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

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
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

        $user->fill($data);
        $saved = $user->save();
        $user->refresh();

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
                'message' => 'Perfil atualizado com sucesso!',
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

        return redirect()->route('panel.profile.edit')->with('success', 'Perfil atualizado com sucesso!');
    }
}

