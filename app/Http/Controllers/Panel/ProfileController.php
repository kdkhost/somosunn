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
        return view('panel.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'cep' => ['nullable', 'regex:/^\d{5}-?\d{3}$/'],
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'phone' => 'nullable|string|max:20',
            'doc' => 'nullable|string|max:18',
        ], [
            'cep.regex' => 'Informe um CEP válido.',
            'phone.max' => 'Telefone deve ter no máximo 20 caracteres.',
            'doc.max' => 'Documento deve ter no máximo 18 caracteres.',
        ]);
        $user->fill($request->only([
            'cep', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'phone', 'doc'
        ]));
        $user->save();
        return redirect()->route('panel.profile.edit')->with('success', 'Dados atualizados com sucesso!');
    }
}

