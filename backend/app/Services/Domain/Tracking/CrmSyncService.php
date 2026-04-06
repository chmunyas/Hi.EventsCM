<?php

declare(strict_types=1);

namespace HiEvents\Services\Domain\Tracking;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrmSyncService
{
    /**
     * Sync a purchase/attendee to all configured CRM providers.
     */
    public function syncPurchase(array $integrations, array $contactData): void
    {
        if (!empty($integrations['mailchimp']['enabled'])) {
            $this->syncToMailchimp($integrations['mailchimp'], $contactData);
        }

        if (!empty($integrations['hubspot']['enabled'])) {
            $this->syncToHubspot($integrations['hubspot'], $contactData);
        }
    }

    /**
     * Add/update a subscriber in a Mailchimp list.
     * @see https://mailchimp.com/developer/marketing/api/list-members/
     */
    private function syncToMailchimp(array $config, array $contactData): void
    {
        $apiKey = $config['api_key'] ?? null;
        $listId = $config['list_id'] ?? null;

        if (!$apiKey || !$listId) {
            return;
        }

        // Extract data center from API key (e.g., "abc123-us21" → "us21")
        $dc = substr(strrchr($apiKey, '-'), 1);
        if (!$dc) {
            Log::warning('Mailchimp API key missing data center suffix');
            return;
        }

        $email = $contactData['email'] ?? null;
        if (!$email) {
            return;
        }

        $subscriberHash = md5(strtolower(trim($email)));

        try {
            Http::withBasicAuth('apikey', $apiKey)
                ->put(
                    "https://{$dc}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}",
                    [
                        'email_address' => $email,
                        'status_if_new' => 'subscribed',
                        'merge_fields' => [
                            'FNAME' => $contactData['first_name'] ?? '',
                            'LNAME' => $contactData['last_name'] ?? '',
                        ],
                        'tags' => array_filter([
                            $contactData['event_name'] ?? null,
                            'hi-events-purchase',
                        ]),
                    ]
                );
        } catch (\Throwable $e) {
            Log::warning('Mailchimp sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Create/update a contact in HubSpot.
     * @see https://developers.hubspot.com/docs/api/crm/contacts
     */
    private function syncToHubspot(array $config, array $contactData): void
    {
        $apiKey = $config['api_key'] ?? null;

        if (!$apiKey) {
            return;
        }

        $email = $contactData['email'] ?? null;
        if (!$email) {
            return;
        }

        try {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.hubapi.com/crm/v3/objects/contacts', [
                'properties' => [
                    'email' => $email,
                    'firstname' => $contactData['first_name'] ?? '',
                    'lastname' => $contactData['last_name'] ?? '',
                    'hi_events_last_event' => $contactData['event_name'] ?? '',
                    'hi_events_last_order_date' => now()->toDateString(),
                ],
            ]);
        } catch (\Throwable $e) {
            // HubSpot returns 409 if contact exists; try PATCH update
            if (str_contains($e->getMessage(), '409')) {
                $this->updateHubspotContact($apiKey, $email, $contactData);
            } else {
                Log::warning('HubSpot sync failed: ' . $e->getMessage());
            }
        }
    }

    private function updateHubspotContact(string $apiKey, string $email, array $contactData): void
    {
        try {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->patch("https://api.hubapi.com/crm/v3/objects/contacts/{$email}?idProperty=email", [
                'properties' => [
                    'hi_events_last_event' => $contactData['event_name'] ?? '',
                    'hi_events_last_order_date' => now()->toDateString(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('HubSpot contact update failed: ' . $e->getMessage());
        }
    }
}
