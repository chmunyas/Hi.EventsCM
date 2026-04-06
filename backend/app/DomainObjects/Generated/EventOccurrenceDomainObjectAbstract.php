<?php

namespace HiEvents\DomainObjects\Generated;

abstract class EventOccurrenceDomainObjectAbstract extends \HiEvents\DomainObjects\AbstractDomainObject
{
    final public const SINGULAR_NAME = 'event_occurrence';
    final public const PLURAL_NAME = 'event_occurrences';
    final public const ID = 'id';
    final public const EVENT_ID = 'event_id';
    final public const EVENT_SERIES_ID = 'event_series_id';
    final public const START_DATE = 'start_date';
    final public const END_DATE = 'end_date';
    final public const STATUS = 'status';
    final public const CAPACITY_OVERRIDE = 'capacity_override';
    final public const PRICE_OVERRIDE = 'price_override';
    final public const METADATA = 'metadata';
    final public const TICKETS_SOLD = 'tickets_sold';
    final public const CREATED_AT = 'created_at';
    final public const UPDATED_AT = 'updated_at';
    final public const DELETED_AT = 'deleted_at';

    protected int $id;
    protected int $event_id;
    protected int $event_series_id;
    protected string $start_date;
    protected ?string $end_date = null;
    protected string $status = 'active';
    protected ?int $capacity_override = null;
    protected ?float $price_override = null;
    protected ?array $metadata = null;
    protected int $tickets_sold = 0;
    protected ?string $created_at = null;
    protected ?string $updated_at = null;
    protected ?string $deleted_at = null;

    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'event_id' => $this->event_id ?? null,
            'event_series_id' => $this->event_series_id ?? null,
            'start_date' => $this->start_date ?? null,
            'end_date' => $this->end_date ?? null,
            'status' => $this->status ?? null,
            'capacity_override' => $this->capacity_override ?? null,
            'price_override' => $this->price_override ?? null,
            'metadata' => $this->metadata ?? null,
            'tickets_sold' => $this->tickets_sold ?? null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
            'deleted_at' => $this->deleted_at ?? null,
        ];
    }

    public function setId(int $id): self { $this->id = $id; return $this; }
    public function getId(): int { return $this->id; }
    public function setEventId(int $event_id): self { $this->event_id = $event_id; return $this; }
    public function getEventId(): int { return $this->event_id; }
    public function setEventSeriesId(int $event_series_id): self { $this->event_series_id = $event_series_id; return $this; }
    public function getEventSeriesId(): int { return $this->event_series_id; }
    public function setStartDate(string $start_date): self { $this->start_date = $start_date; return $this; }
    public function getStartDate(): string { return $this->start_date; }
    public function setEndDate(?string $end_date): self { $this->end_date = $end_date; return $this; }
    public function getEndDate(): ?string { return $this->end_date; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setCapacityOverride(?int $capacity_override): self { $this->capacity_override = $capacity_override; return $this; }
    public function getCapacityOverride(): ?int { return $this->capacity_override; }
    public function setPriceOverride(?float $price_override): self { $this->price_override = $price_override; return $this; }
    public function getPriceOverride(): ?float { return $this->price_override; }
    public function setMetadata(?array $metadata): self { $this->metadata = $metadata; return $this; }
    public function getMetadata(): ?array { return $this->metadata; }
    public function setTicketsSold(int $tickets_sold): self { $this->tickets_sold = $tickets_sold; return $this; }
    public function getTicketsSold(): int { return $this->tickets_sold; }
    public function setCreatedAt(?string $created_at): self { $this->created_at = $created_at; return $this; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function setUpdatedAt(?string $updated_at): self { $this->updated_at = $updated_at; return $this; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
}
