<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\Tracking;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Interfaces\EventSettingsRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetTrackingIntegrationsAction extends BaseAction
{
    public function __construct(
        private readonly EventSettingsRepositoryInterface $eventSettingsRepository,
    ) {
    }

    public function __invoke(Request $request, int $event_id): JsonResponse
    {
        $this->isActionAuthorized($event_id);

        $settings = $this->eventSettingsRepository->findFirstWhere([
            ['event_id', '=', $event_id],
        ]);

        $integrations = $settings ? json_decode($settings->getTrackingIntegrations() ?? '{}', true) : [];

        // Mask sensitive fields for display
        foreach (['ga4', 'tiktok', 'mailchimp', 'hubspot'] as $provider) {
            if (isset($integrations[$provider])) {
                foreach (['api_secret', 'access_token', 'api_key'] as $field) {
                    if (isset($integrations[$provider][$field])) {
                        $val = $integrations[$provider][$field];
                        $integrations[$provider][$field] = substr($val, 0, 4) . str_repeat('*', max(0, strlen($val) - 4));
                    }
                }
            }
        }

        return $this->jsonResponse(['integrations' => $integrations]);
    }
}
