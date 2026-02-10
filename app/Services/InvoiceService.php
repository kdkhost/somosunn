<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Setting;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceService
{
    public function createFromOrder(Order $order, ?int $createdBy = null): Invoice
    {
        $existing = Invoice::query()->where('order_id', (int) $order->id)->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($order, $createdBy) {
            $invoice = Invoice::create([
                'user_id' => (int) $order->user_id,
                'order_id' => (int) $order->id,
                'created_by' => $createdBy,
                'status' => $order->status === 'paid' ? 'paid' : 'issued',
                'currency' => (string) ($order->currency ?: 'BRL'),
                'issued_at' => $order->created_at ?? now(),
                'paid_at' => $order->status === 'paid' ? now() : null,
                'metadata' => [
                    'source' => 'order',
                    'gateway' => $order->gateway,
                    'transaction_id' => $order->transaction_id,
                    'order_metadata' => $order->metadata,
                ],
            ]);

            $items = $order->items()->get();
            $subtotal = 0.0;

            foreach ($items as $idx => $orderItem) {
                $quantity = (int) ($orderItem->quantity ?: 1);
                $unit = (float) ($orderItem->price ?: 0);
                $lineTotal = round($unit * $quantity, 2);
                $subtotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id' => (int) $invoice->id,
                    'item_type' => (string) ($orderItem->item_type ?? ''),
                    'item_id' => (int) ($orderItem->item_id ?? 0) ?: null,
                    'description' => (string) ($orderItem->title ?: ($orderItem->item_type . ' #' . $orderItem->item_id)),
                    'quantity' => max(1, $quantity),
                    'unit_price' => $unit,
                    'total_price' => $lineTotal,
                    'data' => $orderItem->data,
                    'sort_order' => $idx,
                ]);
            }

            $subtotal = round($subtotal, 2);

            $invoice->subtotal = $subtotal;
            $invoice->discount_amount = 0;
            $invoice->total_amount = $subtotal;
            $invoice->ensureNumber();
            $invoice->save();

            return $invoice;
        });
    }

    public function createManual(array $invoiceData, array $items, ?int $createdBy = null): Invoice
    {
        return DB::transaction(function () use ($invoiceData, $items, $createdBy) {
            $invoice = Invoice::create([
                'user_id' => (int) $invoiceData['user_id'],
                'order_id' => $invoiceData['order_id'] ?? null,
                'created_by' => $createdBy,
                'status' => (string) ($invoiceData['status'] ?? 'issued'),
                'currency' => (string) ($invoiceData['currency'] ?? 'BRL'),
                'issued_at' => $invoiceData['issued_at'] ?? now(),
                'due_at' => $invoiceData['due_at'] ?? null,
                'notes' => $invoiceData['notes'] ?? null,
                'metadata' => array_merge(['source' => 'manual'], (array) ($invoiceData['metadata'] ?? [])),
            ]);

            $subtotal = 0.0;
            foreach ($items as $idx => $item) {
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $unit = (float) ($item['unit_price'] ?? 0);
                $lineTotal = round($unit * $quantity, 2);
                $subtotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id' => (int) $invoice->id,
                    'item_type' => $item['item_type'] ?? null,
                    'item_id' => $item['item_id'] ?? null,
                    'description' => (string) ($item['description'] ?? ''),
                    'quantity' => $quantity,
                    'unit_price' => $unit,
                    'total_price' => $lineTotal,
                    'data' => $item['data'] ?? null,
                    'sort_order' => (int) ($item['sort_order'] ?? $idx),
                ]);
            }

            $subtotal = round($subtotal, 2);
            $discount = (float) ($invoiceData['discount_amount'] ?? 0);
            $discount = max(0, round($discount, 2));

            $invoice->subtotal = $subtotal;
            $invoice->discount_amount = $discount;
            $invoice->total_amount = max(0, round($subtotal - $discount, 2));

            if (($invoice->status ?? '') === 'paid' && !$invoice->paid_at) {
                $invoice->paid_at = now();
            }

            $invoice->ensureNumber();
            $invoice->save();

            return $invoice;
        });
    }

    public function queueInvoiceEmail(Invoice $invoice, bool $force = false): void
    {
        if (!$invoice->user || empty($invoice->user->email)) {
            return;
        }

        if (!$force && $invoice->email_sent_at) {
            return;
        }

        if (!$force && $invoice->email_queued_at && $invoice->email_queued_at->gt(now()->subMinutes(15))) {
            return;
        }

        $invoice->email_queued_at = now();
        $invoice->save();

        $dispatch = function () use ($invoice, $force) {
            \App\Jobs\SendInvoiceEmailJob::dispatch((int) $invoice->id, $force);
        };

        try {
            DB::afterCommit($dispatch);
        } catch (\Throwable $e) {
            $dispatch();
        }
    }

    public function issueAndQueueForOrder(Order $order, bool $force = false): ?Invoice
    {
        try {
            $invoice = $this->createFromOrder($order, null);

            if ($order->status === 'paid') {
                $dirty = false;
                if (($invoice->status ?? '') !== 'paid') {
                    $invoice->status = 'paid';
                    $dirty = true;
                }
                if (!$invoice->paid_at) {
                    $invoice->paid_at = now();
                    $dirty = true;
                }
                if ($dirty) {
                    $invoice->save();
                }
            }

            $this->queueInvoiceEmail($invoice, $force);
            return $invoice;
        } catch (\Throwable $e) {
            Log::warning('Falha ao emitir/filtrar fatura para pedido #' . $order->id . ': ' . $e->getMessage());
            return null;
        }
    }

    public function generatePdfBytes(Invoice $invoice): string
    {
        $invoice->loadMissing(['user', 'items', 'order']);

        $html = view('pdf.invoice', [
            'invoice' => $invoice,
            'company' => $this->companyInfo(),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function companyInfo(): array
    {
        return [
            'name' => (string) (Setting::get('company_name') ?: (Setting::get('app_name') ?: config('app.name'))),
            'email' => (string) (Setting::get('company_email') ?: ''),
            'phone' => (string) (Setting::get('company_phone') ?: ''),
            'address' => trim(implode(', ', array_filter([
                (string) (Setting::get('company_address') ?: ''),
                (string) (Setting::get('company_number') ?: ''),
                (string) (Setting::get('company_district') ?: ''),
                (string) (Setting::get('company_city') ?: ''),
                (string) (Setting::get('company_state') ?: ''),
                (string) (Setting::get('company_zip') ?: ''),
            ], static fn($v) => trim($v) !== ''))),
            'site' => url('/'),
            'logo' => $this->resolveLogoPath(),
            'logo_url' => $this->resolveLogoUrl(),
            'primary_color' => (string) (Setting::get('site_color_primary') ?: '#1F5EDB'),
        ];
    }

    private function resolveLogoPath(): ?string
    {
        $logo = Setting::get('logo_image');
        if (!$logo) {
            return null;
        }

        $path = public_path($logo);
        if (file_exists($path)) {
            return $path;
        }

        // Check if it's already a full path
        if (file_exists($logo)) {
            return $logo;
        }

        // Try storage/ if it starts with it
        if (str_starts_with($logo, 'storage/')) {
            $storagePath = public_path($logo);
            if (file_exists($storagePath)) {
                return $storagePath;
            }
        }

        return null;
    }

    private function resolveLogoUrl(): ?string
    {
        $logo = Setting::get('logo_image');
        if (!$logo) {
            return null;
        }

        if (filter_var($logo, FILTER_VALIDATE_URL)) {
            return $logo;
        }

        return asset($logo);
    }

    public function normalizeMoney($value): float
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return 0.0;
        }

        $raw = str_replace(['R$', ' ', "\u{00A0}"], '', $raw);

        // If brazilian format, normalize: 1.234,56 -> 1234.56
        if (str_contains($raw, ',')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }

        $raw = preg_replace('/[^0-9.\\-]/', '', $raw);
        return round((float) $raw, 2);
    }
}
