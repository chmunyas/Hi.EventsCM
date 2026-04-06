<?php

namespace HiEvents\Resources\ReportPreset;

use HiEvents\DomainObjects\ReportPresetDomainObject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReportPresetDomainObject
 */
class ReportPresetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getId(),
            'account_id' => $this->getAccountId(),
            'event_id' => $this->getEventId(),
            'created_by_user_id' => $this->getCreatedByUserId(),
            'name' => $this->getName(),
            'report_type' => $this->getReportType(),
            'filters' => $this->getFilters(),
            'columns' => $this->getColumns(),
            'sort_by' => $this->getSortBy(),
            'sort_direction' => $this->getSortDirection(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
        ];
    }
}
