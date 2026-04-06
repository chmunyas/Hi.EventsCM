<?php

namespace HiEvents\Http\Actions\ReportPresets;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\Generated\ReportPresetDomainObjectAbstract;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Interfaces\ReportPresetRepositoryInterface;
use HiEvents\Resources\ReportPreset\ReportPresetResource;
use Illuminate\Http\JsonResponse;

class GetReportPresetsAction extends BaseAction
{
    public function __construct(
        private readonly ReportPresetRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $presets = $this->repository->findWhere([
            ReportPresetDomainObjectAbstract::ACCOUNT_ID => $this->getAuthenticatedAccountId(),
            ReportPresetDomainObjectAbstract::EVENT_ID => $eventId,
        ]);

        return $this->resourceResponse(
            resource: ReportPresetResource::class,
            data: $presets,
        );
    }
}
