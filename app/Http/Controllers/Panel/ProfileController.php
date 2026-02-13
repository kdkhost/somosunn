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

        $request->merge([
            'phone' => $this->normalizePhone($request->input('phone')),
            'doc' => $this->normalizeDocument($request->input('doc')),
            'cep' => $this->normalizeCep($request->input('cep')),
            'state' => $this->normalizeState($request->input('state')),
        ]);

        $docType = $request->input('doc_type', 'cpf');

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'phone' => ['nullable', 'string', 'max:16', 'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/'],
            'doc_type' => 'required|in:cpf,cnpj',
            'doc' => [
                'nullable',
                'string',
                'max:18',
                function ($attribute, $value, $fail) use ($docType) {
                    if ($value) {
                        if ($docType === 'cpf' && !preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $value)) {
                            return $fail('O CPF informado não é válido.');
                        }
                        if ($docType === 'cnpj' && !preg_match('/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/', $value)) {
                            return $fail('O CNPJ informado não é válido.');
                        }
                    }
                },
            ],
            'occupation' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:100',
            'segment' => 'nullable|string|max:120',
            'interests' => 'nullable|array',
            'interests.*' => 'string|max:100',
            'bio' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'cover_photo' => 'nullable|image|max:4096',

            // Endereço
            'cep' => ['nullable', 'string', 'size:9', 'regex:/^\d{5}-\d{3}$/'],
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => ['nullable', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],

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

        // Salvar interesses como string separada por vírgula para compatibilidade, ou como array/json se o model permitir
        if (isset($data['interests']) && is_array($data['interests'])) {
            $data['interests'] = implode(',', array_filter($data['interests']));
        }

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

    private function normalizeDigits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?: '';
    }

    private function normalizeCep(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $digits = $this->normalizeDigits($raw);
        if (strlen($digits) !== 8) {
            return $raw;
        }

        return substr($digits, 0, 5) . '-' . substr($digits, 5, 3);
    }

    private function normalizePhone(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $digits = $this->normalizeDigits($raw);
        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6, 4));
        }
        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7, 4));
        }

        return $raw;
    }

    private function normalizeDocument(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $digits = $this->normalizeDigits($raw);
        if (strlen($digits) === 11) {
            return sprintf(
                '%s.%s.%s-%s',
                substr($digits, 0, 3),
                substr($digits, 3, 3),
                substr($digits, 6, 3),
                substr($digits, 9, 2)
            );
        }
        if (strlen($digits) === 14) {
            return sprintf(
                '%s.%s.%s/%s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 3),
                substr($digits, 5, 3),
                substr($digits, 8, 4),
                substr($digits, 12, 2)
            );
        }

        return $raw;
    }

    private function normalizeState(?string $value): ?string
    {
        $raw = strtoupper(trim((string) $value));
        if ($raw === '') {
            return null;
        }

        $lettersOnly = preg_replace('/[^A-Z]/', '', $raw) ?: '';
        if (strlen($lettersOnly) !== 2) {
            return $raw;
        }

        return $lettersOnly;
    }
}
