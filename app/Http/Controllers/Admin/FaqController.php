<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        if ($schemaError = $this->validateSchema()) {
            return $schemaError;
        }

        $contexts = Faq::contextOptions();
        $context = (string) $request->query('context', '');
        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));

        $faqs = Faq::query()
            ->when($context !== '', function ($query) use ($context) {
                $query->where('context', $context);
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('question', 'like', '%' . $q . '%')
                        ->orWhere('answer', 'like', '%' . $q . '%');
                });
            })
            ->orderBy('context')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.faqs.index', compact('faqs', 'contexts', 'context', 'status', 'q'));
    }

    public function create()
    {
        if ($schemaError = $this->validateSchema()) {
            return $schemaError;
        }

        return view('admin.faqs.form', [
            'faq' => new Faq(),
            'contexts' => Faq::contextOptions(),
        ]);
    }

    public function store(Request $request)
    {
        if ($schemaError = $this->validateSchema()) {
            return $schemaError;
        }

        $data = $this->validateData($request);

        $faq = Faq::create($data);

        return response()->json([
            'redirect' => route('admin.faqs.edit', $faq),
            'message' => 'Pergunta criada com sucesso.',
        ]);
    }

    public function edit(Faq $faq)
    {
        if ($schemaError = $this->validateSchema()) {
            return $schemaError;
        }

        return view('admin.faqs.form', [
            'faq' => $faq,
            'contexts' => Faq::contextOptions(),
        ]);
    }

    public function update(Request $request, Faq $faq)
    {
        if ($schemaError = $this->validateSchema()) {
            return $schemaError;
        }

        $data = $this->validateData($request);

        $faq->fill($data);
        $faq->save();

        return response()->json([
            'redirect' => route('admin.faqs.edit', $faq),
            'message' => 'Pergunta atualizada com sucesso.',
        ]);
    }

    public function destroy(Faq $faq)
    {
        if ($schemaError = $this->validateSchema()) {
            return $schemaError;
        }

        $faq->delete();

        return response()->json(['ok' => true]);
    }

    private function validateData(Request $request): array
    {
        $contexts = array_keys(Faq::contextOptions());

        $data = $request->validate([
            'context' => ['required', 'string', Rule::in($contexts)],
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'is_active' => 'nullable|boolean',
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    private function validateSchema()
    {
        $message = 'Seu banco de dados está desatualizado para FAQ. Rode: php artisan migrate';

        try {
            if (!Schema::hasTable('faqs')) {
                return $this->schemaUnavailable($message);
            }

            foreach (['context', 'question', 'answer', 'sort_order', 'is_active'] as $column) {
                if (!Schema::hasColumn('faqs', $column)) {
                    return $this->schemaUnavailable($message);
                }
            }
        } catch (\Throwable $e) {
            return $this->schemaUnavailable('Não foi possível validar a estrutura de FAQ no banco. Rode: php artisan migrate');
        }

        return null;
    }

    private function schemaUnavailable(string $message)
    {
        if (request()->ajax() || request()->wantsJson() || request()->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 422);
        }

        return response()->view('admin.faqs.unavailable', [
            'message' => $message,
        ], 200);
    }
}
