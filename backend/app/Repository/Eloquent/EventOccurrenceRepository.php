<?php

namespace HiEvents\Repository\Eloquent;

use HiEvents\DomainObjects\EventOccurrenceDomainObject;
use HiEvents\Models\EventOccurrence;
use HiEvents\Repository\Interfaces\EventOccurrenceRepositoryInterface;
use Illuminate\Support\Collection;

class EventOccurrenceRepository extends BaseRepository implements EventOccurrenceRepositoryInterface
{
    protected function getModel(): string
    {
        return EventOccurrence::class;
    }

    public function getDomainObject(): string
    {
        return EventOccurrenceDomainObject::class;
    }

    public function findByEventId(int $eventId, ?string $status = null): Collection
    {
        $query = $this->model->where('event_id', $eventId)->orderBy('start_date');

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get()->map(fn($model) => EventOccurrenceDomainObject::hydrateFromModel($model));
    }

    public function findUpcomingByEventId(int $eventId, int $limit = 50): Collection
    {
        return $this->model
            ->where('event_id', $eventId)
            ->where('status', 'active')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit($limit)
            ->get()
            ->map(fn($model) => EventOccurrenceDomainObject::hydrateFromModel($model));
    }

    public function incrementTicketsSold(int $occurrenceId, int $quantity = 1): void
    {
        $this->model->where('id', $occurrenceId)->increment('tickets_sold', $quantity);
    }
}
