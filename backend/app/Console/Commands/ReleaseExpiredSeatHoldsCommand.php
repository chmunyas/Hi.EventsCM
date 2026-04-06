<?php

namespace HiEvents\Console\Commands;

use HiEvents\Services\Domain\Seating\SeatHoldService;
use Illuminate\Console\Command;

class ReleaseExpiredSeatHoldsCommand extends Command
{
    protected $signature = 'seats:release-expired';

    protected $description = 'Release all expired seat holds and return seats to available status';

    public function __construct(
        private readonly SeatHoldService $seatHoldService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $released = $this->seatHoldService->releaseExpiredHolds();

        $this->info("Released {$released} expired seat hold(s).");

        return self::SUCCESS;
    }
}
