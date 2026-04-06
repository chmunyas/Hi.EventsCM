<?php

declare(strict_types=1);

namespace HiEvents\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventOccurrence extends BaseModel
{
    use SoftDeletes;

    protected function getCastMap(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function eventSeries(): BelongsTo
    {
        return $this->belongsTo(EventSeries::class);
    }
}
