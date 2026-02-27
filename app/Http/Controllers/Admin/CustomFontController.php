<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomFont;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomFontController extends Controller
{
    public function index()
    {
        $fonts = CustomFont::where('is_active', true)->orderBy('name')->get();
        return view('admin.fonts.index', compact('fonts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'name' => 'required|string|max:255',
                'type' => 'required|in:file,google_link',
                'font_file' => [
                    'nullable',
                    'required_if:type,file',
                    'file',
                    'max:5120',
                    'mimes:ttf,otf,woff,woff2',
                    'mimetypes:font/ttf,font/otf,font/woff,font/woff2,application/font-sfnt,application/x-font-ttf,application/x-font-opentype,application/font-woff,application/font-woff2,application/x-font-woff,application/octet-stream',
                ],
                'google_font_url' => 'nullable|required_if:type,google_link|url',
                'font_family' => 'required|string|max:255',
            ],
            [
                'font_file.required_if' => 'Selecione um arquivo de fonte.',
                'font_file.mimes' => 'Formato inválido. Use TTF, OTF, WOFF ou WOFF2.',
                'font_file.mimetypes' => 'O arquivo enviado não parece ser uma fonte válida.',
                'font_file.max' => 'O arquivo da fonte deve ter no máximo 5MB.',
                'google_font_url.required_if' => 'Informe a URL do Google Fonts.',
                'google_font_url.url' => 'Informe uma URL válida do Google Fonts.',
            ]
        );

        if ($request->type === 'file' && $request->hasFile('font_file')) {
            $file = $request->file('font_file');
            $extension = strtolower((string) $file->getClientOriginalExtension());
            if (!in_array($extension, ['ttf', 'otf', 'woff', 'woff2'], true)) {
                throw ValidationException::withMessages([
                    'font_file' => ['Formato inválido. Use TTF, OTF, WOFF ou WOFF2.'],
                ]);
            }

            $uploadDir = public_path('uploads/fonts');
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            $fileName = 'font_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $data['file_path'] = 'uploads/fonts/' . $fileName;
            $data['google_font_url'] = null;
        } elseif ($request->type === 'google_link') {
            $googleUrl = trim((string) ($data['google_font_url'] ?? ''));

            if (stripos($googleUrl, '<link') !== false) {
                if (preg_match('/href=["\']([^"\']+)["\']/', $googleUrl, $matches)) {
                    $googleUrl = trim((string) ($matches[1] ?? ''));
                }
            }

            $urlValidator = Validator::make(
                ['google_font_url' => $googleUrl],
                ['google_font_url' => 'required|url'],
                ['google_font_url.url' => 'Informe uma URL válida do Google Fonts.']
            );

            if ($urlValidator->fails()) {
                throw ValidationException::withMessages($urlValidator->errors()->toArray());
            }

            $data['google_font_url'] = $googleUrl;
            $data['file_path'] = null;
        }

        $data['uploaded_by'] = auth()->id();
        
        CustomFont::create($data);

        return response()->json(['success' => true, 'message' => 'Fonte adicionada com sucesso!']);
    }

    public function destroy(CustomFont $font)
    {
        // Soft delete by marking as inactive
        $font->update(['is_active' => false]);
        
        return response()->json(['success' => true, 'message' => 'Fonte removida.']);
    }

    // API endpoint to get all active fonts (for certificate editor)
    public function getActiveFonts()
    {
        $fonts = CustomFont::where('is_active', true)
            ->select('id', 'name', 'font_family', 'type', 'google_font_url', 'file_path')
            ->get();
        
        return response()->json($fonts);
    }
}
