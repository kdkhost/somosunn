<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * Atualiza a preferência de tema do usuário.
     */
    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark'
        ]);

        $user = auth()->user();
        if ($user) {
            $user->update(['theme_pref' => $request->theme]);
            return response()->json(['message' => 'Tema atualizado com sucesso!', 'theme' => $request->theme]);
        }

        return response()->json(['message' => 'Usuário não autenticado.'], 401);
    }
}
