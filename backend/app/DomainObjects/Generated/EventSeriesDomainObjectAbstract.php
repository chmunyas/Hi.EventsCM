<?php

namespace HiEvents\DomainObjects\Generated;

abstract class EventSeriesDomainObjectAbstract extends \HiEvents\DomainObjects\AbstractDomainObject
{
    final public const SINGULAR_NAME = 'event_series';
    final public const PLURAL_NAME = 'event_series';
    final public const ID = 'id';
    final public const EVENT_ID = 'event_id';
    final public const RECURRENCE_TYPE = 'recurrence_type';
    final public const RRULE = 'rrule';
    final public const CUSTOM_DATES = 'custom_dates';
    final public const SLOTS_PER_DAY = 'slots_per_day';
    final public const SERIES_STARTS_AT = 'series_starts_at';
    final public const SERIES_ENDS_AT = 'series_ends_at';
    final public const IS_ACTIVE = 'is_active';
    final public const CREATED_AT = 'created_at';
    final public const UPDATED_AT = 'updated_at';
    final public const DELETED_AT = 'deleted_at';

    protected int $id;
    protected int $event_id;
    protected string $recurrence_type;
    protected ?string $rrule = null;
    protected ?array $custom_dates = null;
    protected int $slots_per_day = 1;
    protected string $series_starts_at;
    protected ?string $series_ends_at = null;
    protected bool $is_active = true;
    protected ?string $created_at = null;
    protected ?string $updated_at = null;
    protected ?string $deleted_at = null;

    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'event_id' => $this->event_id ?? null,
            'recurrence_type' => $this->recurrence_type ?? null,
            'rrule' => $this->rrule ?? null,
            'custom_dates' => $this->custom_dates ?? null,
            'slots_per_day' => $this->slots_per_day ?? null,
            'series_starts_at' => $this->series_starts_at ?? null,
            'series_ends_at' => $this->series_ends_at ?? null,
            'is_active' => $this->is_active ?? null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
            'deleted_at' => $this->deleted_at ?? null,
        ];
    }

    public function setId(int $id): self { $this->id = $id; return $this; }
    public function getId(): int { return $this->id; }
    public function setEventId(int $event_id): self { $this->event_id = $event_id; return $this; }
    public function getEventId(): int { return $this->event_id; }
    public function setRecurrenceType(string $recurrence_type): self { $this->recurrence_type = $recurrence_type; return $this; }
    public function getRecurrenceType(): string { return $this->recurrence_type; }
    public function setRrule(?string $rrule): self { $this->rrule = $rrule; return $this; }
    public function getRrule(): ?string { return $this->rrule; }
    public function setCustomDates(?array $custom_dates): self { $this->custom_dates = $custom_dates; return $this; }
    public function getCustomDates(): ?array { return $this->custom_dates; }
    public function setSlotsPerDay(int $slots_per_day): self { $this->slots_per_day = $slots_per_day; return $this; }
    public function getSlotsPerDay(): int { return $this->slots_per_day; }
    public function setSeriesStartsAt(string $series_starts_at): self { $this->series_starts_at = $series_starts_at; return $this; }
    public function getSeriesStartsAt(): string { return $this->series_starts_at; }
    public function setSeriesEndsAt(?string $series_ends_at): self { $this->series_ends_at = $series_ends_at; return $this; }
    public function getSeriesEndsAt(): ?string { return $this->series_ends_at; }
    public function setIsActive(bool $is_active): self { $this->is_active = $is_active; return $this; }
    public function getIsActive(): bool { return $this->is_active; }
    public function setCreatedAt(?string $created_at): self { $this->created_at = $created_at; return $this; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function setUpdatedAt(?string $updated_at): self { $this->updated_at = $updated_at; return $this; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
}
