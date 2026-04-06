<?php

declare(strict_types=1);

namespace HiEvents\Services\Domain\Seating;

use HiEvents\Exceptions\SeatNotAvailableException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SeatHoldService
{
    private const DEFAULT_HOLD_MINUTES = 10;

    /**
     * Atomically hold a seat using pessimistic locking.
     *
     * @throws SeatNotAvailableException
     */
    public function holdSeat(
        int    $seatId,
        int    $chartId,
        int    $eventId,
        string $sessionToken,
        ?string $heldByIp = null,
        int    $holdMinutes = self::DEFAULT_HOLD_MINUTES,
    ): array {
        return DB::transaction(function () use ($seatId, $chartId, $eventId, $sessionToken, $heldByIp, $holdMinutes) {
            // Pessimistic lock on the seat row
            $seat = DB::table('seats')
                ->where('id', $seatId)
                ->where('seating_chart_id', $chartId)
                ->lockForUpdate()
                ->first();

            if (!$seat) {
                throw new SeatNotAvailableException('Seat not found.');
            }

            if ($seat->status !== 'available') {
                throw new SeatNotAvailableException(
                    "Seat is not available (current status: {$seat->status})."
                );
            }

            $expiresAt = Carbon::now()->addMinutes($holdMinutes);

            // Mark seat as held
            DB::table('seats')
                ->where('id', $seatId)
                ->update([
                    'status' => 'held',
                    'updated_at' => now(),
                ]);

            // Insert the hold record
            $holdId = DB::table('seat_holds')->insertGetId([
                'seat_id' => $seatId,
                'seating_chart_id' => $chartId,
                'event_id' => $eventId,
                'session_token' => $sessionToken,
                'held_by_ip' => $heldByIp,
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'hold_id' => $holdId,
                'seat_id' => $seatId,
                'session_token' => $sessionToken,
                'expires_at' => $expiresAt->toIso8601String(),
            ];
        });
    }

    /**
     * Release a specific seat hold and set the seat back to available.
     */
    public function releaseHold(int $seatId, string $sessionToken): bool
    {
        return DB::transaction(function () use ($seatId, $sessionToken) {
            $hold = DB::table('seat_holds')
                ->where('seat_id', $seatId)
                ->where('session_token', $sessionToken)
                ->lockForUpdate()
                ->first();

            if (!$hold) {
                return false;
            }

            DB::table('seat_holds')->where('id', $hold->id)->delete();

            DB::table('seats')
                ->where('id', $seatId)
                ->where('status', 'held')
                ->update([
                    'status' => 'available',
                    'updated_at' => now(),
                ]);

            return true;
        });
    }

    /**
     * Confirm a held seat to sold status (used during checkout completion).
     *
     * @throws SeatNotAvailableException
     */
    public function confirmHold(int $seatId, string $sessionToken, int $attendeeId, ?int $productId = null): void
    {
        DB::transaction(function () use ($seatId, $sessionToken, $attendeeId, $productId) {
            $hold = DB::table('seat_holds')
                ->where('seat_id', $seatId)
                ->where('session_token', $sessionToken)
                ->lockForUpdate()
                ->first();

            if (!$hold) {
                throw new SeatNotAvailableException('No active hold found for this seat and session.');
            }

            if (Carbon::parse($hold->expires_at)->isPast()) {
                // Clean up the expired hold
                DB::table('seat_holds')->where('id', $hold->id)->delete();
                DB::table('seats')
                    ->where('id', $seatId)
                    ->where('status', 'held')
                    ->update(['status' => 'available', 'updated_at' => now()]);

                throw new SeatNotAvailableException('Seat hold has expired.');
            }

            // Transition seat from held to sold
            $updated = DB::table('seats')
                ->where('id', $seatId)
                ->where('status', 'held')
                ->update([
                    'status' => 'sold',
                    'attendee_id' => $attendeeId,
                    'product_id' => $productId,
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                throw new SeatNotAvailableException('Seat is no longer in held state.');
            }

            // Remove the hold record
            DB::table('seat_holds')->where('id', $hold->id)->delete();
        });
    }

    /**
     * Release all expired seat holds. Called by the scheduled command.
     *
     * @return int Number of holds released.
     */
    public function releaseExpiredHolds(): int
    {
        $expiredHolds = DB::table('seat_holds')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        $released = 0;

        foreach ($expiredHolds as $hold) {
            DB::transaction(function () use ($hold, &$released) {
                DB::table('seat_holds')->where('id', $hold->id)->delete();

                DB::table('seats')
                    ->where('id', $hold->seat_id)
                    ->where('status', 'held')
                    ->update([
                        'status' => 'available',
                        'updated_at' => now(),
                    ]);

                $released++;
            });
        }

        return $released;
    }
}
