<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class FarmerRevenueService
{
    /**
     * @return array{period: string, total: float, points: array<int, array{label: string, total: float}>}
     */
    public function summarize(int $farmerId, string $period, ?int $year, ?int $month): array
    {
        $receivedAt = 'COALESCE(completed_at, updated_at)';
        $query = Order::query()
            ->where('farmer_id', $farmerId)
            ->where('status', 'COMPLETED');

        $label = match ($period) {
            'day' => "DATE($receivedAt)",
            'month' => "DATE_FORMAT($receivedAt, '%Y-%m')",
            default => "YEAR($receivedAt)",
        };

        if ($period === 'day') {
            $query->whereRaw("YEAR($receivedAt) = ?", [$year])
                ->whereRaw("MONTH($receivedAt) = ?", [$month]);
        } elseif ($period === 'month') {
            $query->whereRaw("YEAR($receivedAt) = ?", [$year]);
        }

        $rows = $query
            ->selectRaw("$label as label, COALESCE(SUM(total_price), 0) as total")
            ->groupBy(DB::raw($label))
            ->orderBy('label')
            ->get();

        $points = $rows->map(fn ($row) => [
            'label' => (string) $row->label,
            'total' => (float) $row->total,
        ])->all();

        return [
            'period' => $period,
            'total' => array_sum(array_column($points, 'total')),
            'points' => $points,
        ];
    }
}
