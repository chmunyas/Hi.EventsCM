<?php

namespace HiEvents\Repository\Interfaces;

use HiEvents\DomainObjects\EventOccurrenceDomainObject;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<EventOccurrenceDomainObject>
 */
interface EventOccurrenceRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, ?string $status = null): Collection;

    public function findUpcomingByEventId(int $eventId, int $limit = 50): Collection;

    public function incrementTicketsSold(int $occurrenceId, int $quantity = 1): void;
}
