<?php

namespace HiEvents\Models;

use HiEvents\DomainObjects\Generated\ReportPresetDomainObjectAbstract;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportPreset extends BaseModel
{
    use SoftDeletes;

    protected function getCastMap(): array
    {
        return [
            ReportPresetDomainObjectAbstract::FILTERS => 'array',
            ReportPresetDomainObjectAbstract::COLUMNS => 'array',
        ];
    }

    protected function getFillableFields(): array
    {
        return [
            ReportPresetDomainObjectAbstract::ACCOUNT_ID,
            ReportPresetDomainObjectAbstract::EVENT_ID,
            ReportPresetDomainObjectAbstract::CREATED_BY_USER_ID,
            ReportPresetDomainObjectAbstract::NAME,
            ReportPresetDomainObjectAbstract::REPORT_TYPE,
            ReportPresetDomainObjectAbstract::FILTERS,
            ReportPresetDomainObjectAbstract::COLUMNS,
            ReportPresetDomainObjectAbstract::SORT_BY,
            ReportPresetDomainObjectAbstract::SORT_DIRECTION,
        ];
    }
}
