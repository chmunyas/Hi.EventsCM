<?php

namespace HiEvents\Repository\Eloquent;

use HiEvents\DomainObjects\ReportPresetDomainObject;
use HiEvents\Models\ReportPreset;
use HiEvents\Repository\Interfaces\ReportPresetRepositoryInterface;

/**
 * @extends BaseRepository<ReportPresetDomainObject>
 */
class ReportPresetRepository extends BaseRepository implements ReportPresetRepositoryInterface
{
    protected function getModel(): string
    {
        return ReportPreset::class;
    }

    public function getDomainObject(): string
    {
        return ReportPresetDomainObject::class;
    }
}
