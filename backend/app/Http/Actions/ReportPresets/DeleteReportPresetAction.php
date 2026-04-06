<?php

namespace HiEvents\Http\Actions\ReportPresets;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\Generated\ReportPresetDomainObjectAbstract;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Interfaces\ReportPresetRepositoryInterface;
use Illuminate\Http\JsonResponse;

class DeleteReportPresetAction extends BaseAction
{
    public function __construct(
        private readonly ReportPresetRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $eventId, int $presetId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $this->repository->deleteWhere([
            ReportPresetDomainObjectAbstract::ID => $presetId,
            ReportPresetDomainObjectAbstract::ACCOUNT_ID => $this->getAuthenticatedAccountId(),
            ReportPresetDomainObjectAbstract::EVENT_ID => $eventId,
        ]);

        return $this->deletedResponse();
    }
}
