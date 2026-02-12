<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\PointsLog;
use App\Services\PointsService;
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
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'doc' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:100',
            'segment' => 'nullable|string|max:120',
            'interests' => 'nullable|string|max:500',
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
        ]);

        $data['show_email_public'] = $request->has('show_email_public');
        $data['show_phone_public'] = $request->has('show_phone_public');
        $data['show_address_public'] = $request->has('show_address_public');
        $data['hide_profile'] = $request->has('hide_profile');

        // Upload avatar
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            if ($user->photo && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
            }

            $file = $request->file('photo');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/imagens/avatars');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = 'uploads/imagens/avatars/' . $filename;
            $file->move($directory, $filename);

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
            $directory = public_path('uploads/imagens/covers');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = 'uploads/imagens/covers/' . $filename;
            $file->move($directory, $filename);

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

