<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomFont;
use Illuminate\Http\Request;

class CustomFontController extends Controller
{
    public function index()
    {
        $fonts = CustomFont::where('is_active', true)->orderBy('name')->get();
        return view('admin.fonts.index', compact('fonts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:file,google_link',
            'font_file' => 'required_if:type,file|file|mimes:ttf,otf,woff,woff2|max:5120',
            'google_font_url' => 'required_if:type,google_link|url',
            'font_family' => 'required|string|max:255',
        ]);

        if ($request->type === 'file' && $request->hasFile('font_file')) {
            $file = $request->file('font_file');
            $fileName = 'font_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/fonts'), $fileName);
            $data['file_path'] = 'uploads/fonts/' . $fileName;
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
