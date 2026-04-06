<?php

declare(strict_types=1);

namespace HiEvents\Services\Domain\Tracking;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackingIntegrationService
{
    /**
     * Fire a purchase/conversion event to all configured tracking providers.
     *
     * @param array $integrations The tracking_integrations config from event_settings
     * @param array $eventData    Standardized event payload
     */
    public function firePurchaseEvent(array $integrations, array $eventData): void
    {
        if (!empty($integrations['ga4']['enabled'])) {
            $this->fireGa4Event($integrations['ga4'], $eventData);
        }

        if (!empty($integrations['tiktok']['enabled'])) {
            $this->fireTikTokEvent($integrations['tiktok'], $eventData);
        }
    }

    /**
     * GA4 Measurement Protocol server-side event.
     * @see https://developers.google.com/analytics/devguides/collection/protocol/ga4
     */
    private function fireGa4Event(array $config, array $eventData): void
    {
        $measurementId = $config['measurement_id'] ?? null;
        $apiSecret = $config['api_secret'] ?? null;

        if (!$measurementId || !$apiSecret) {
            return;
        }

        $payload = [
            'client_id' => $eventData['client_id'] ?? $this->generateClientId(),
            'events' => [
                [
                    'name' => 'purchase',
                    'params' => [
                        'transaction_id' => $eventData['order_id'] ?? '',
                        'value' => $eventData['total'] ?? 0,
                        'currency' => $eventData['currency'] ?? 'USD',
                        'items' => array_map(fn($item) => [
                            'item_id' => (string) ($item['product_id'] ?? ''),
                            'item_name' => $item['product_name'] ?? '',
                            'quantity' => $item['quantity'] ?? 1,
                            'price' => $item['price'] ?? 0,
                        ], $eventData['items'] ?? []),
                    ],
                ],
            ],
        ];

        try {
            Http::post(
                "https://www.google-analytics.com/mp/collect?measurement_id={$measurementId}&api_secret={$apiSecret}",
                $payload
            );
        } catch (\Throwable $e) {
            Log::warning('GA4 tracking failed: ' . $e->getMessage());
        }
    }

    /**
     * TikTok Events API server-side event.
     * @see https://business-api.tiktok.com/portal/docs?id=1741601162187777
     */
    private function fireTikTokEvent(array $config, array $eventData): void
    {
        $pixelId = $config['pixel_id'] ?? null;
        $accessToken = $config['access_token'] ?? null;

        if (!$pixelId || !$accessToken) {
            return;
        }

        $payload = [
            'pixel_code' => $pixelId,
            'event' => 'CompletePayment',
            'event_id' => $eventData['order_id'] ?? uniqid('evt_'),
            'timestamp' => now()->toIso8601String(),
            'context' => [
                'user_agent' => $eventData['user_agent'] ?? '',
                'ip' => $eventData['ip'] ?? '',
            ],
            'properties' => [
                'contents' => array_map(fn($item) => [
                    'content_id' => (string) ($item['product_id'] ?? ''),
                    'content_name' => $item['product_name'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'price' => $item['price'] ?? 0,
                ], $eventData['items'] ?? []),
                'value' => $eventData['total'] ?? 0,
                'currency' => $eventData['currency'] ?? 'USD',
            ],
        ];

        try {
            Http::withHeaders([
                'Access-Token' => $accessToken,
                'Content-Type' => 'application/json',
            ])->post('https://business-api.tiktok.com/open_api/v1.3/pixel/track/', [
                'pixel_code' => $pixelId,
                'event' => 'CompletePayment',
                'event_id' => $payload['event_id'],
                'timestamp' => $payload['timestamp'],
                'context' => $payload['context'],
                'properties' => $payload['properties'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('TikTok tracking failed: ' . $e->getMessage());
        }
    }

    private function generateClientId(): string
    {
        return bin2hex(random_bytes(16));
    }
}
