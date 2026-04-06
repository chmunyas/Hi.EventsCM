<?php

declare(strict_types=1);

namespace HiEvents\Services\Domain\Pos;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PosSessionReconciliationService
{
    /**
     * Generate a reconciliation summary for a POS session.
     */
    public function generateSummary(int $sessionId): array
    {
        $transactions = DB::table('pos_transactions')
            ->where('pos_session_id', $sessionId)
            ->where('status', 'completed')
            ->get();

        return $this->computeSummary($sessionId, $transactions);
    }

    /**
     * Compute the summary from a collection of transactions (testable without DB).
     */
    public function computeSummary(int $sessionId, Collection $transactions): array
    {
        $breakdown = ['cash' => 0.0, 'card' => 0.0, 'free' => 0.0];
        $counts = ['cash' => 0, 'card' => 0, 'free' => 0];

        foreach ($transactions as $tx) {
            $method = is_array($tx) ? $tx['payment_method'] : $tx->payment_method;
            $amount = is_array($tx) ? (float) $tx['amount'] : (float) $tx->amount;

            if (isset($breakdown[$method])) {
                $breakdown[$method] += $amount;
                $counts[$method]++;
            }
        }

        $totalSales = array_sum($breakdown);

        $createdAts = $transactions->map(fn($tx) => is_array($tx) ? ($tx['created_at'] ?? null) : ($tx->created_at ?? null))->filter();

        return [
            'session_id' => $sessionId,
            'total_transactions' => $transactions->count(),
            'total_sales' => round($totalSales, 2),
            'breakdown' => array_map(fn($v) => round($v, 2), $breakdown),
            'transaction_count' => $counts,
            'first_transaction_at' => $createdAts->min(),
            'last_transaction_at' => $createdAts->max(),
        ];
    }
}
