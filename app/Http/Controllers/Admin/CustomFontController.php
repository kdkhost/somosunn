<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomFont;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        if ($request->type === 'file' && $request->hasFile('font_file')) {
            [$detectedName, $detectedFamily] = $this->detectFontNameAndFamily($request->file('font_file'));

            $request->merge([
                'name' => $this->normalizeInputValue($request->input('name')) ?: $detectedName,
                'font_family' => $this->normalizeInputValue($request->input('font_family')) ?: $detectedFamily,
            ]);
        }

        $data = $request->validate(
            [
                'name' => 'required|string|max:255',
                'type' => 'required|in:file,google_link',
                'font_file' => $this->fontFileRules(),
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

    public function detectMetadata(Request $request)
    {
        $request->validate(
            [
                'font_file' => $this->fontFileRules(true),
            ],
            [
                'font_file.required' => 'Selecione um arquivo de fonte.',
                'font_file.mimes' => 'Formato invalido. Use TTF, OTF, WOFF ou WOFF2.',
                'font_file.mimetypes' => 'O arquivo enviado nao parece ser uma fonte valida.',
                'font_file.max' => 'O arquivo da fonte deve ter no maximo 5MB.',
            ]
        );

        $file = $request->file('font_file');
        if (!$file instanceof UploadedFile) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo invalido.',
            ], 422);
        }

        [$name, $fontFamily] = $this->detectFontNameAndFamily($file);

        return response()->json([
            'success' => true,
            'name' => $name,
            'font_family' => $fontFamily,
        ]);
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

    private function fontFileRules(bool $forceRequired = false): array
    {
        $rules = [
            'nullable',
            'required_if:type,file',
            'file',
            'max:5120',
            'mimes:ttf,otf,woff,woff2',
            'mimetypes:font/ttf,font/otf,font/woff,font/woff2,application/font-sfnt,application/x-font-ttf,application/x-font-opentype,application/font-woff,application/font-woff2,application/x-font-woff,application/octet-stream',
        ];

        if ($forceRequired) {
            $rules = array_merge(['required'], $rules);
        }

        return $rules;
    }

    private function detectFontNameAndFamily(UploadedFile $file): array
    {
        $fallback = $this->fallbackFontNameFromFile($file);
        $name = $fallback;
        $family = $fallback;

        try {
            if (class_exists(\FontLib\Font::class)) {
                $font = \FontLib\Font::load($file->getRealPath());

                if ($font && method_exists($font, 'parse') && method_exists($font, 'getData')) {
                    $font->parse();
                    $records = $font->getData('name', 'records');

                    if (is_array($records) && !empty($records)) {
                        $preferredFamily = $this->getNameRecordValue($records, 16);
                        $fontName = $this->getNameRecordValue($records, 1);
                        $fullName = $this->getNameRecordValue($records, 4);
                        $postScriptName = $this->getNameRecordValue($records, 6);

                        $detectedFamily = $preferredFamily ?: ($fontName ?: ($fullName ?: $postScriptName));
                        $detectedName = $fullName ?: ($detectedFamily ?: $postScriptName);

                        if ($detectedName !== '') {
                            $name = $this->normalizeFontText($detectedName);
                        }

                        if ($detectedFamily !== '') {
                            $family = $this->normalizeFontText($detectedFamily);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Keep fallback values when metadata extraction fails.
        }

        $name = $this->normalizeFontText($name);
        $family = trim($this->normalizeFontText($family), " \t\n\r\0\x0B'\"");

        if ($name === '') {
            $name = $fallback;
        }

        if ($family === '') {
            $family = $name;
        }

        return [
            mb_substr($name, 0, 255),
            mb_substr($family, 0, 255),
        ];
    }

    private function getNameRecordValue(array $records, int $recordId): string
    {
        if (!isset($records[$recordId])) {
            return '';
        }

        $record = $records[$recordId];

        if (is_object($record) && isset($record->string)) {
            return $this->normalizeFontText((string) $record->string);
        }

        if (is_array($record) && isset($record['string'])) {
            return $this->normalizeFontText((string) $record['string']);
        }

        return $this->normalizeFontText((string) $record);
    }

    private function fallbackFontNameFromFile(UploadedFile $file): string
    {
        $fileName = (string) pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = preg_replace('/[_-]+/u', ' ', $fileName) ?? $fileName;
        $fileName = $this->normalizeFontText($fileName);

        if ($fileName === '') {
            return 'Fonte Personalizada';
        }

        return mb_convert_case($fileName, MB_CASE_TITLE, 'UTF-8');
    }

    private function normalizeInputValue($value): string
    {
        if (!is_string($value)) {
            return '';
        }

        return $this->normalizeFontText($value);
    }

    private function normalizeFontText(string $value): string
    {
        $value = str_replace("\0", '', $value);
        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }
}
