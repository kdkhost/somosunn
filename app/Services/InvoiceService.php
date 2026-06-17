<?php

namespace App\Services;

use App\Jobs\SendInvoiceEmailJob;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Support\EmailQueueSettings;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function createFromOrder(Order $order, ?int $createdBy = null): Invoice
    {
        $existing = Invoice::query()->where('order_id', (int) $order->id)->first();
        if ($existing) {
            $source = (string) data_get($existing->metadata, 'source', 'order');

            if ($source !== 'order') {
                return $existing;
            }

            return DB::transaction(fn () => $this->syncFromOrder($existing, $order, $createdBy));
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
            ]);

            return $this->syncFromOrder($invoice, $order, $createdBy);
        });
    }

    private function syncFromOrder(Invoice $invoice, Order $order, ?int $createdBy = null): Invoice
    {
        $order->loadMissing('items');

        $couponCode = $order->coupon_code;
        $grossAmount = (float) $order->gross_amount;
        $discountAmount = min($grossAmount, (float) $order->financial_discount_amount);
        $couponNote = $couponCode && $discountAmount > 0
            ? 'Cupom utilizado: ' . $couponCode . ' - desconto de R$ ' . number_format($discountAmount, 2, ',', '.') . '.'
            : null;

        $invoice->fill([
            'user_id' => (int) $order->user_id,
            'order_id' => (int) $order->id,
            'created_by' => $invoice->created_by ?: $createdBy,
            'status' => $order->status === 'paid' ? 'paid' : 'issued',
            'currency' => (string) ($order->currency ?: 'BRL'),
            'issued_at' => $invoice->issued_at ?: ($order->created_at ?? now()),
            'paid_at' => $order->status === 'paid' ? ($invoice->paid_at ?: now()) : null,
            'notes' => $couponNote ?: $invoice->notes,
            'metadata' => array_merge($invoice->metadata ?? [], [
                'source' => 'order',
                'gateway' => $order->gateway,
                'transaction_id' => $order->transaction_id,
                'gross_amount' => round($grossAmount, 2),
                'discount_amount' => round($discountAmount, 2),
                'net_amount' => round((float) $order->total_amount, 2),
                'coupon' => $couponCode ? [
                    'code' => $couponCode,
                    'discount_amount' => round($discountAmount, 2),
                ] : null,
                'order_metadata' => $order->metadata,
            ]),
        ]);

        $invoice->save();
        $invoice->items()->delete();

        $subtotal = 0.0;
        $items = $order->items;
        $itemsCount = max(1, $items->count());

        foreach ($items as $idx => $orderItem) {
            $quantity = max(1, (int) ($orderItem->quantity ?: 1));
            $unit = $this->invoiceUnitPriceForOrderItem($orderItem, $grossAmount, $itemsCount);
            $lineTotal = round($unit * $quantity, 2);
            $subtotal += $lineTotal;

            InvoiceItem::create([
                'invoice_id' => (int) $invoice->id,
                'item_type' => (string) ($orderItem->item_type ?? ''),
                'item_id' => (int) ($orderItem->item_id ?? 0) ?: null,
                'description' => (string) ($orderItem->title ?: ($orderItem->item_type . ' #' . $orderItem->item_id)),
                'quantity' => $quantity,
                'unit_price' => $unit,
                'total_price' => $lineTotal,
                'data' => array_merge($orderItem->data ?? [], [
                    'financial' => [
                        'gross_unit_price' => $unit,
                        'net_unit_price' => round((float) $orderItem->price, 2),
                        'coupon_code' => $couponCode,
                        'discount_amount' => round($discountAmount, 2),
                    ],
                ]),
                'sort_order' => $idx,
            ]);
        }

        $subtotal = round($subtotal, 2);
        if ($subtotal <= 0 && $grossAmount > 0) {
            $subtotal = round($grossAmount, 2);
        }

        $discountAmount = min($subtotal, $discountAmount);

        $invoice->subtotal = $subtotal;
        $invoice->discount_amount = round($discountAmount, 2);
        $invoice->total_amount = max(0, round($subtotal - $discountAmount, 2));
        $invoice->ensureNumber();
        $invoice->save();

        return $invoice->fresh(['items', 'order', 'user']);
    }

    private function invoiceUnitPriceForOrderItem(OrderItem $orderItem, float $orderGrossAmount, int $itemsCount): float
    {
        $unit = (float) $orderItem->gross_unit_price;
        if ($unit > 0) {
            return round($unit, 2);
        }

        if ($itemsCount === 1 && $orderGrossAmount > 0) {
            return round($orderGrossAmount / max(1, (int) $orderItem->quantity), 2);
        }

        return round((float) $orderItem->price, 2);
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

    public function queueInvoiceEmail(Invoice $invoice, bool $force = false, bool $sync = false): void
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

        if ($sync || !EmailQueueSettings::shouldQueue()) {
            try {
                SendInvoiceEmailJob::dispatchSync((int) $invoice->id, $force);
            } catch (\Throwable $e) {
                $invoice->email_queued_at = null;
                $invoice->save();

                throw $e;
            }

            return;
        }

        $dispatch = function () use ($invoice, $force) {
            $job = new SendInvoiceEmailJob((int) $invoice->id, $force);
            dispatch(EmailQueueSettings::applyToQueueable($job));
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

        $company = $this->companyInfo();

        $html = view('pdf.invoice', [
            'invoice' => $invoice,
            'company' => $company,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', $company['invoice_font_family'] ?? 'DejaVu Sans');

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
            'logo' => $this->resolveLogoBase64(),
            'logo_url' => $this->resolveLogoUrl(),
            'primary_color' => (string) (Setting::get('invoice_primary_color') ?: Setting::get('site_color_primary') ?: '#1F5EDB'),
            // Configurações do editor de faturas
            'invoice_primary_color' => (string) (Setting::get('invoice_primary_color') ?: '#1F5EDB'),
            'invoice_secondary_color' => (string) (Setting::get('invoice_secondary_color') ?: '#177FD6'),
            'invoice_text_color' => (string) (Setting::get('invoice_text_color') ?: '#1f2937'),
            'invoice_bg_color' => (string) (Setting::get('invoice_bg_color') ?: '#f9fafb'),
            'invoice_logo_position' => (string) (Setting::get('invoice_logo_position') ?: 'left'),
            'invoice_logo_max_height' => (int) (Setting::get('invoice_logo_max_height') ?: 60),
            'invoice_font_family' => (string) (Setting::get('invoice_font_family') ?: 'DejaVu Sans'),
            'invoice_show_company_address' => (bool) (int) (Setting::get('invoice_show_company_address') ?? '1'),
            'invoice_show_company_phone' => (bool) (int) (Setting::get('invoice_show_company_phone') ?? '1'),
            'invoice_show_company_email' => (bool) (int) (Setting::get('invoice_show_company_email') ?? '1'),
            'invoice_show_due_date' => (bool) (int) (Setting::get('invoice_show_due_date') ?? '1'),
            'invoice_show_status_badge' => (bool) (int) (Setting::get('invoice_show_status_badge') ?? '1'),
            'invoice_show_notes' => (bool) (int) (Setting::get('invoice_show_notes') ?? '1'),
            'invoice_show_footer' => (bool) (int) (Setting::get('invoice_show_footer') ?? '1'),
            'invoice_footer_text' => (string) (Setting::get('invoice_footer_text') ?: 'Obrigado pela sua preferência!'),
            'invoice_header_text' => (string) (Setting::get('invoice_header_text') ?: 'FATURA'),
            'invoice_custom_css' => (string) (Setting::get('invoice_custom_css') ?: ''),
        ];
    }

    private function resolveLogoBase64(): ?string
    {
        $path = $this->resolveLogoPath();
        if (!$path || !file_exists($path)) {
            return null;
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        if ($type === 'svg') {
            $type = 'svg+xml';
        }

        try {
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            return $base64;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveLogoPath(): ?string
    {
        $keys = ['logo_image', 'logo_admin', 'logo_front', 'logo_auth'];
        $logo = null;

        foreach ($keys as $key) {
            $logo = Setting::get($key);
            if ($logo) {
                break;
            }
        }

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
        $keys = ['logo_image', 'logo_admin', 'logo_front', 'logo_auth'];
        $logo = null;

        foreach ($keys as $key) {
            $logo = Setting::get($key);
            if ($logo) {
                break;
            }
        }

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
