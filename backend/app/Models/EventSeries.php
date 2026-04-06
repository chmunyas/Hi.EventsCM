<?php

declare(strict_types=1);

namespace HiEvents\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSeries extends BaseModel
{
    use SoftDeletes;

    protected function getCastMap(): array
    {
        return [
            'custom_dates' => 'array',
            'is_active' => 'boolean',
            'series_starts_at' => 'datetime',
            'series_ends_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(EventOccurrence::class);
    }
}
