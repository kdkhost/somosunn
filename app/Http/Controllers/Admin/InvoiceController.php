<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $invoices = Invoice::query()
            ->with(['user', 'order'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('number', 'like', '%' . $q . '%')
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('name', 'like', '%' . $q . '%')
                            ->orWhere('email', 'like', '%' . $q . '%');
                    });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.invoices.index', compact('invoices', 'q'));
    }

    public function create()
    {
        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('admin.invoices.form', [
            'invoice' => new Invoice(),
            'users' => $users,
        ]);
    }

    public function store(Request $request, InvoiceService $service)
    {
        $data = $this->validateData($request);
        $data['discount_amount'] = $service->normalizeMoney($data['discount_amount'] ?? 0);

        $items = $this->parseItemsFromRequest($request, $service);
        if (count($items) === 0) {
            return back()->withInput()->with('error', 'Adicione pelo menos 1 item na fatura.');
        }

        $invoice = $service->createManual($data, $items, (int) auth()->id());

        if ($request->boolean('send_email')) {
            $service->queueInvoiceEmail($invoice, true);
        }

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Fatura criada com sucesso.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'items', 'order']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['items']);

        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('admin.invoices.form', compact('invoice', 'users'));
    }

    public function update(Request $request, Invoice $invoice, InvoiceService $service)
    {
        $data = $this->validateData($request, $invoice->id);
        $data['discount_amount'] = $service->normalizeMoney($data['discount_amount'] ?? 0);

        $items = $this->parseItemsFromRequest($request, $service);
        if (count($items) === 0) {
            return back()->withInput()->with('error', 'Adicione pelo menos 1 item na fatura.');
        }

        // Update header
        $invoice->fill($data);
        $invoice->save();

        // Replace items
        $invoice->items()->delete();
        foreach ($items as $i) {
            $invoice->items()->create($i);
        }

        // Recalculate totals
        $invoice->load('items');
        $subtotal = (float) $invoice->items->sum('total_price');
        $invoice->subtotal = round($subtotal, 2);
        $invoice->discount_amount = max(0, (float) ($invoice->discount_amount ?? 0));
        $invoice->total_amount = max(0, round(((float) $invoice->subtotal) - ((float) $invoice->discount_amount), 2));

        if (($invoice->status ?? '') === 'paid' && !$invoice->paid_at) {
            $invoice->paid_at = now();
        }

        $invoice->ensureNumber();
        $invoice->save();

        if ($request->boolean('send_email')) {
            $service->queueInvoiceEmail($invoice, true);
        }

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Fatura atualizada.');
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $invoice->delete();

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Fatura removida.');
    }

    public function send(Invoice $invoice, Request $request, InvoiceService $service)
    {
        $force = $request->boolean('force', true);
        $service->queueInvoiceEmail($invoice, $force);

        return back()->with('success', 'Envio de fatura enfileirado.');
    }

    public function pdf(Invoice $invoice, InvoiceService $service)
    {
        $pdfBytes = $service->generatePdfBytes($invoice);
        $filename = 'Fatura-' . ($invoice->number ?: $invoice->id) . '.pdf';

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function issueForOrder(Order $order, InvoiceService $service)
    {
        $invoice = $service->createFromOrder($order, (int) auth()->id());
        $service->queueInvoiceEmail($invoice, true);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Fatura emitida para o pedido #' . $order->id);
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::in(['draft', 'issued', 'paid', 'cancelled'])],
            'currency' => ['nullable', 'string', 'max:10'],
            'issued_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'discount_amount' => ['nullable'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['currency'] = $data['currency'] ?: 'BRL';
        $data['issued_at'] = $data['issued_at'] ?? now();

        // discount_amount can come as "R$ 1.234,56"
        $data['discount_amount'] = $data['discount_amount'] ?? 0;

        return $data;
    }

    private function parseItemsFromRequest(Request $request, InvoiceService $service): array
    {
        $descriptions = (array) $request->input('items_description', []);
        $quantities = (array) $request->input('items_quantity', []);
        $unitPrices = (array) $request->input('items_unit_price', []);

        $items = [];

        foreach ($descriptions as $idx => $desc) {
            $desc = trim((string) $desc);
            if ($desc === '') {
                continue;
            }

            $qty = isset($quantities[$idx]) ? (int) $quantities[$idx] : 1;
            $qty = max(1, $qty);

            $unit = isset($unitPrices[$idx]) ? $service->normalizeMoney($unitPrices[$idx]) : 0.0;
            $lineTotal = round($unit * $qty, 2);

            $items[] = [
                'description' => $desc,
                'quantity' => $qty,
                'unit_price' => $unit,
                'total_price' => $lineTotal,
                'sort_order' => (int) $idx,
            ];
        }

        return $items;
    }
}
