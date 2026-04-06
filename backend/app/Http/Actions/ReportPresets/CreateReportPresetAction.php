<?php

namespace HiEvents\Http\Actions\ReportPresets;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\Generated\ReportPresetDomainObjectAbstract;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\ResponseCodes;
use HiEvents\Repository\Interfaces\ReportPresetRepositoryInterface;
use HiEvents\Resources\ReportPreset\ReportPresetResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreateReportPresetAction extends BaseAction
{
    public function __construct(
        private readonly ReportPresetRepositoryInterface $repository,
    ) {
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_type' => 'required|string|max:100',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'sort_by' => 'nullable|string|max:100',
            'sort_direction' => 'nullable|string|in:asc,desc',
        ]);

        $preset = $this->repository->create([
            ReportPresetDomainObjectAbstract::ACCOUNT_ID => $this->getAuthenticatedAccountId(),
            ReportPresetDomainObjectAbstract::EVENT_ID => $eventId,
            ReportPresetDomainObjectAbstract::CREATED_BY_USER_ID => $this->getAuthenticatedUser()->getId(),
            ReportPresetDomainObjectAbstract::NAME => $validated['name'],
            ReportPresetDomainObjectAbstract::REPORT_TYPE => $validated['report_type'],
            ReportPresetDomainObjectAbstract::FILTERS => json_encode($validated['filters'] ?? []),
            ReportPresetDomainObjectAbstract::COLUMNS => json_encode($validated['columns'] ?? []),
            ReportPresetDomainObjectAbstract::SORT_BY => $validated['sort_by'] ?? null,
            ReportPresetDomainObjectAbstract::SORT_DIRECTION => $validated['sort_direction'] ?? 'asc',
        ]);

        return $this->resourceResponse(
            resource: ReportPresetResource::class,
            data: $preset,
            statusCode: ResponseCodes::HTTP_CREATED,
        );
    }
}
