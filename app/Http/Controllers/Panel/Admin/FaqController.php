<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('faqs.view');

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
            ->paginate(15);

        $faqs->appends($request->all());

        return view('panel.admin.faqs.index', compact('faqs', 'contexts', 'context', 'status', 'q'));
    }

    public function create()
    {
        $this->ensurePermission('faqs.create');
        return view('panel.admin.faqs.form', [
            'faq' => new Faq(),
            'contexts' => Faq::contextOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('faqs.create');
        $data = $this->validateData($request);
        $faq = Faq::create($data);

        return redirect()->route('panel.admin.faqs.index')->with('success', 'Pergunta criada com sucesso.');
    }

    public function edit(Faq $faq)
    {
        $this->ensurePermission('faqs.edit');
        return view('panel.admin.faqs.form', [
            'faq' => $faq,
            'contexts' => Faq::contextOptions(),
        ]);
    }

    public function update(Request $request, Faq $faq)
    {
        $this->ensurePermission('faqs.edit');
        $data = $this->validateData($request);
        $faq->update($data);

        return redirect()->route('panel.admin.faqs.index')->with('success', 'Pergunta atualizada com sucesso.');
    }

    public function destroy(Faq $faq)
    {
        $this->ensurePermission('faqs.delete');
        $faq->delete();
        return redirect()->route('panel.admin.faqs.index')->with('success', 'FAQ removido.');
    }

    private function validateData(Request $request): array
    {
        $contexts = array_keys(Faq::contextOptions());
        $data = $request->validate([
            'context' => ['required', 'string', Rule::in($contexts)],
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);
        return $data;
    }

    private function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm)) {
            abort(403);
        }
    }
}
