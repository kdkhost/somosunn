<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\InvoiceService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceEditorController extends Controller
{
    /**
     * Configurações padrão do editor de faturas.
     */
    private const DEFAULTS = [
        'invoice_primary_color' => '#1F5EDB',
        'invoice_secondary_color' => '#177FD6',
        'invoice_text_color' => '#1f2937',
        'invoice_bg_color' => '#f9fafb',
        'invoice_logo_position' => 'left',
        'invoice_logo_max_height' => 60,
        'invoice_font_family' => 'DejaVu Sans',
        'invoice_show_company_address' => true,
        'invoice_show_company_phone' => true,
        'invoice_show_company_email' => true,
        'invoice_show_due_date' => true,
        'invoice_show_status_badge' => true,
        'invoice_show_notes' => true,
        'invoice_show_footer' => true,
        'invoice_footer_text' => 'Obrigado pela sua preferência!',
        'invoice_header_text' => 'FATURA',
        'invoice_custom_css' => '',
    ];

    /**
     * Exibe o editor visual de faturas.
     */
    public function index()
    {
        $settings = [];
        foreach (self::DEFAULTS as $key => $default) {
            $value = Setting::get($key);
            if ($value === null || $value === '') {
                $settings[$key] = $default;
            } else {
                // Converter booleans armazenados como string
                if (is_string($value) && in_array($value, ['0', '1'], true)) {
                    $settings[$key] = (bool) (int) $value;
                } else {
                    $settings[$key] = $value;
                }
            }
        }

        // Dados da empresa para a aba "Dados"
        $companyData = [
            'company_name' => (string) (Setting::get('company_name') ?: ''),
            'company_cnpj' => (string) (Setting::get('company_cnpj') ?: ''),
            'company_address' => (string) (Setting::get('company_address') ?: ''),
            'company_number' => (string) (Setting::get('company_number') ?: ''),
            'company_district' => (string) (Setting::get('company_district') ?: ''),
            'company_city' => (string) (Setting::get('company_city') ?: ''),
            'company_state' => (string) (Setting::get('company_state') ?: ''),
            'company_zip' => (string) (Setting::get('company_zip') ?: ''),
            'company_phone' => (string) (Setting::get('company_phone') ?: ''),
            'company_email' => (string) (Setting::get('company_email') ?: ''),
        ];

        return view('admin.invoices.editor', compact('settings', 'companyData'));
    }

    /**
     * Salva as configurações do editor (AJAX).
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'invoice_primary_color' => 'nullable|string|max:7',
            'invoice_secondary_color' => 'nullable|string|max:7',
            'invoice_text_color' => 'nullable|string|max:7',
            'invoice_bg_color' => 'nullable|string|max:7',
            'invoice_logo_position' => 'nullable|in:left,center,right',
            'invoice_logo_max_height' => 'nullable|integer|min:30|max:120',
            'invoice_font_family' => 'nullable|in:DejaVu Sans,Helvetica,Courier',
            'invoice_show_company_address' => 'nullable',
            'invoice_show_company_phone' => 'nullable',
            'invoice_show_company_email' => 'nullable',
            'invoice_show_due_date' => 'nullable',
            'invoice_show_status_badge' => 'nullable',
            'invoice_show_notes' => 'nullable',
            'invoice_show_footer' => 'nullable',
            'invoice_footer_text' => 'nullable|string|max:500',
            'invoice_header_text' => 'nullable|string|max:100',
            'invoice_custom_css' => 'nullable|string|max:5000',
        ]);

        $booleanFields = [
            'invoice_show_company_address',
            'invoice_show_company_phone',
            'invoice_show_company_email',
            'invoice_show_due_date',
            'invoice_show_status_badge',
            'invoice_show_notes',
            'invoice_show_footer',
        ];

        foreach (self::DEFAULTS as $key => $default) {
            if (array_key_exists($key, $validated)) {
                $value = $validated[$key];
                if (in_array($key, $booleanFields, true)) {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                }
                Setting::set($key, $value ?? $default, 'invoice');
            }
        }

        Log::channel('security')->info('Configurações do editor de faturas salvas', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'N/A',
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configurações salvas com sucesso!',
        ]);
    }

    /**
     * Gera preview do PDF com as configurações atuais.
     */
    public function preview(Request $request)
    {
        $invoiceService = app(InvoiceService::class);
        $company = $invoiceService->companyInfo();

        // Criar fatura fictícia para preview
        $fakeInvoice = $this->buildFakeInvoice();

        $html = view('pdf.invoice', [
            'invoice' => $fakeInvoice,
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

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview-fatura.pdf"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Restaura configurações padrão.
     */
    public function resetDefaults(Request $request)
    {
        foreach (self::DEFAULTS as $key => $default) {
            $value = $default;
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            Setting::set($key, (string) $value, 'invoice');
        }

        Log::channel('security')->info('Configurações do editor de faturas restauradas para padrão', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'N/A',
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configurações restauradas para os valores padrão!',
            'settings' => self::DEFAULTS,
        ]);
    }

    /**
     * Cria um objeto fake de Invoice para preview.
     */
    private function buildFakeInvoice(): object
    {
        $items = collect([
            (object) [
                'description' => 'Plano Premium Anual',
                'item_type' => 'plan',
                'item_id' => 1,
                'quantity' => 1,
                'unit_price' => 297.00,
                'total_price' => 297.00,
                'sort_order' => 0,
            ],
            (object) [
                'description' => 'Curso de Marketing Digital',
                'item_type' => 'course',
                'item_id' => 5,
                'quantity' => 1,
                'unit_price' => 149.90,
                'total_price' => 149.90,
                'sort_order' => 1,
            ],
            (object) [
                'description' => 'Ingresso Evento Networking',
                'item_type' => 'event',
                'item_id' => 12,
                'quantity' => 2,
                'unit_price' => 75.00,
                'total_price' => 150.00,
                'sort_order' => 2,
            ],
        ]);

        $user = (object) [
            'name' => 'João da Silva',
            'email' => 'joao.silva@exemplo.com.br',
        ];

        return (object) [
            'id' => 1042,
            'number' => '#FAT-2025-1042',
            'status' => 'issued',
            'issued_at' => now(),
            'due_at' => now()->addDays(15),
            'paid_at' => null,
            'subtotal' => 596.90,
            'discount_amount' => 50.00,
            'total_amount' => 546.90,
            'notes' => "Pagamento via boleto bancário.\nVencimento em 15 dias corridos.",
            'user' => $user,
            'items' => $items,
            'order' => null,
        ];
    }
}
