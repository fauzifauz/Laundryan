<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Carbon;

class AttendancePayrollService
{
    public const ALPHA_THRESHOLD = 4;

    public const ALPHA_DEDUCTION_RATE = 0.05;

    /**
     * Count Alpha (unexcused absence) days in a payroll month.
     */
    public static function countAlphaDays(int $userId, int $month, int $year): int
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $records = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($record) => Carbon::parse($record->date)->toDateString());

        $alphaCount = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $dateStr = $current->toDateString();
            $record = $records->get($dateStr);

            if (!$record) {
                $alphaCount++;
            } elseif ($record->status === 'absent') {
                $alphaCount++;
            } elseif (
                in_array($record->status, ['leave', 'permit'], true)
                && $record->approval_status === 'rejected'
            ) {
                $alphaCount++;
            }

            $current->addDay();
        }

        return $alphaCount;
    }

    /**
     * Calculate one-time 5% alpha deduction from net salary before alpha penalty.
     *
     * @return array{count: int, deduction: float}
     */
    public static function calculateAlphaDeduction(
        int $userId,
        int $month,
        int $year,
        float $amount,
        float $bonus,
        float $potonganBeforeAlpha
    ): array {
        $alphaCount = static::countAlphaDays($userId, $month, $year);

        if ($alphaCount < self::ALPHA_THRESHOLD) {
            return ['count' => $alphaCount, 'deduction' => 0];
        }

        $netBeforeAlpha = max(0, $amount + $bonus - $potonganBeforeAlpha);
        $deduction = round($netBeforeAlpha * self::ALPHA_DEDUCTION_RATE, 2);

        return ['count' => $alphaCount, 'deduction' => $deduction];
    }
}
