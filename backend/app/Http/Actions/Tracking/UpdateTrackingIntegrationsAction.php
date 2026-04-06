<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\Tracking;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Interfaces\EventSettingsRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateTrackingIntegrationsAction extends BaseAction
{
    public function __construct(
        private readonly EventSettingsRepositoryInterface $eventSettingsRepository,
    ) {
    }

    public function __invoke(Request $request, int $event_id): JsonResponse
    {
        $this->isActionAuthorized($event_id);

        $validated = $request->validate([
            'ga4' => 'nullable|array',
            'ga4.measurement_id' => 'required_with:ga4|string|max:50',
            'ga4.api_secret' => 'required_with:ga4|string|max:200',
            'ga4.enabled' => 'boolean',
            'tiktok' => 'nullable|array',
            'tiktok.pixel_id' => 'required_with:tiktok|string|max:100',
            'tiktok.access_token' => 'required_with:tiktok|string|max:500',
            'tiktok.enabled' => 'boolean',
            'mailchimp' => 'nullable|array',
            'mailchimp.api_key' => 'required_with:mailchimp|string|max:500',
            'mailchimp.list_id' => 'required_with:mailchimp|string|max:50',
            'mailchimp.enabled' => 'boolean',
            'hubspot' => 'nullable|array',
            'hubspot.api_key' => 'required_with:hubspot|string|max:500',
            'hubspot.enabled' => 'boolean',
        ]);

        $this->eventSettingsRepository->updateWhere(
            attributes: ['tracking_integrations' => json_encode($validated)],
            where: [['event_id', '=', $event_id]],
        );

        return $this->jsonResponse(['message' => 'Tracking integrations updated']);
    }
}
