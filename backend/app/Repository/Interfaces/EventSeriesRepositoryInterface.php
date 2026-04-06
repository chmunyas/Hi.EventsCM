<?php

namespace HiEvents\Repository\Interfaces;

use HiEvents\DomainObjects\EventSeriesDomainObject;

/**
 * @extends RepositoryInterface<EventSeriesDomainObject>
 */
interface EventSeriesRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId): ?EventSeriesDomainObject;
}
