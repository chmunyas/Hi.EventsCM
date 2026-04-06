<?php

namespace HiEvents\Repository\Eloquent;

use HiEvents\DomainObjects\EventSeriesDomainObject;
use HiEvents\Models\EventSeries;
use HiEvents\Repository\Interfaces\EventSeriesRepositoryInterface;

class EventSeriesRepository extends BaseRepository implements EventSeriesRepositoryInterface
{
    protected function getModel(): string
    {
        return EventSeries::class;
    }

    public function getDomainObject(): string
    {
        return EventSeriesDomainObject::class;
    }

    public function findByEventId(int $eventId): ?EventSeriesDomainObject
    {
        $model = $this->model->where('event_id', $eventId)->first();

        if (!$model) {
            return null;
        }

        return EventSeriesDomainObject::hydrateFromModel($model);
    }
}
