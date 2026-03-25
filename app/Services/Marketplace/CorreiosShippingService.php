<?php

namespace App\Services\Marketplace;

use App\Models\SellerStore;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CorreiosShippingService
{
    public function quote(SellerStore $store, Collection $cartItems, array $destination): array
    {
        $physicalItems = $cartItems->filter(fn(array $row) => $row['product']->isPhysical())->values();
        if ($physicalItems->isEmpty()) {
            return [];
        }

        $token = trim((string) Setting::get('correios_api_token', ''));
        if ($token === '') {
            throw new RuntimeException('Token dos Correios nao configurado.');
        }

        $originCep = preg_replace('/\D/', '', (string) ($store->user->cep ?? ''));
        $destinationCep = preg_replace('/\D/', '', (string) ($destination['postal_code'] ?? ''));

        if (strlen($originCep) !== 8) {
            throw new RuntimeException('O vendedor precisa informar um CEP valido para calcular o frete.');
        }

        if (strlen($destinationCep) !== 8) {
            throw new RuntimeException('Informe um CEP valido para calcular o frete.');
        }

        $package = $this->packageMetrics($physicalItems);
        $baseUrl = rtrim((string) Setting::get('correios_api_base_url', 'https://api.correios.com.br'), '/');

        $services = collect([
            [
                'code' => trim((string) Setting::get('correios_service_code_pac', '03298')),
                'label' => 'PAC',
            ],
            [
                'code' => trim((string) Setting::get('correios_service_code_sedex', '03220')),
                'label' => 'SEDEX',
            ],
        ])->filter(fn(array $service) => $service['code'] !== '');

        $quotes = [];
        foreach ($services as $service) {
            $pricePayload = $this->request(
                $baseUrl . '/preco/v1/nacional/' . rawurlencode($service['code']),
                $token,
                [
                    'cepOrigem' => $originCep,
                    'cepDestino' => $destinationCep,
                    'psObjeto' => $package['weight'],
                    'tpObjeto' => 2,
                    'comprimento' => $package['length'],
                    'largura' => $package['width'],
                    'altura' => $package['height'],
                    'diametro' => 0,
                ]
            );

            $deadlinePayload = $this->request(
                $baseUrl . '/prazo/v1/nacional/' . rawurlencode($service['code']),
                $token,
                [
                    'cepOrigem' => $originCep,
                    'cepDestino' => $destinationCep,
                ]
            );

            $amount = $this->extractAmount($pricePayload);
            $deliveryDays = $this->extractDeliveryDays($deadlinePayload);

            if ($amount === null || $deliveryDays === null) {
                continue;
            }

            $quotes[] = [
                'service_code' => $service['code'],
                'service_name' => $service['label'],
                'amount' => $amount,
                'delivery_days' => $deliveryDays,
                'payload' => [
                    'price' => $pricePayload,
                    'deadline' => $deadlinePayload,
                    'package' => $package,
                ],
            ];
        }

        return $quotes;
    }

    private function packageMetrics(Collection $physicalItems): array
    {
        $weightGrams = (int) max(1, $physicalItems->sum(fn(array $row) => ((int) ($row['product']->weight_grams ?? 0)) * (int) $row['quantity']));
        $height = (int) max(1, $physicalItems->sum(fn(array $row) => ((int) ($row['product']->height_cm ?? 0)) * (int) $row['quantity']));
        $width = (int) max(11, $physicalItems->max(fn(array $row) => (int) ($row['product']->width_cm ?? 0)));
        $length = (int) max(16, $physicalItems->max(fn(array $row) => (int) ($row['product']->length_cm ?? 0)));

        return [
            'weight' => max(1, round($weightGrams / 1000, 3)),
            'height' => $height,
            'width' => $width,
            'length' => $length,
        ];
    }

    private function request(string $url, string $token, array $query): array
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(15)
            ->get($url, $query);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao consultar os Correios.');
        }

        return (array) $response->json();
    }

    private function extractAmount(array $payload): ?float
    {
        foreach (['pcFinal', 'preco', 'valor', 'price'] as $key) {
            $candidate = data_get($payload, $key);
            if (is_numeric($candidate)) {
                return round((float) $candidate, 2);
            }

            if (is_string($candidate)) {
                $normalized = str_replace(['.', ','], ['', '.'], preg_replace('/[^\d,\.]/', '', $candidate) ?? '');
                if (is_numeric($normalized)) {
                    return round((float) $normalized, 2);
                }
            }
        }

        return null;
    }

    private function extractDeliveryDays(array $payload): ?int
    {
        foreach (['prazoEntrega', 'prazo', 'deliveryDays'] as $key) {
            $candidate = data_get($payload, $key);
            if (is_numeric($candidate)) {
                return (int) $candidate;
            }
        }

        return null;
    }
}
