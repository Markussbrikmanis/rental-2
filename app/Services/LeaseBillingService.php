<?php

namespace App\Services;

use App\Enums\ChargeFrequency;
use App\Enums\ChargeIntervalUnit;
use App\Models\Lease;
use App\Models\LeaseChargeRule;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class LeaseBillingService
{
    /**
     * @return Collection<int, array{period_from: Carbon, period_to: Carbon}>
     */
    public function duePeriodsUpToDate(Lease $lease, LeaseChargeRule $rule, CarbonInterface $targetDate): Collection
    {
        $targetDate = Carbon::parse($targetDate)->startOfDay();
        $effectiveStart = $this->effectiveStart($lease, $rule);
        $effectiveEnd = $rule->effective_to ? Carbon::parse($rule->effective_to)->endOfDay() : null;

        $cursor = $effectiveStart->copy();
        $periods = collect();

        while ($cursor->lte($targetDate)) {
            [$periodFrom, $periodTo] = $this->periodRange($cursor, $rule);

            if ($effectiveEnd && $periodFrom->gt($effectiveEnd)) {
                break;
            }

            $periodTo = $effectiveEnd && $periodTo->gt($effectiveEnd)
                ? $effectiveEnd->copy()
                : $periodTo;

            $periods->push([
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
            ]);

            $cursor = $this->nextPeriodStart($periodFrom, $rule);
        }

        return $periods;
    }

    public function dueDateForLease(Lease $lease, CarbonInterface $periodFrom): Carbon
    {
        $periodFrom = Carbon::parse($periodFrom);
        $day = min(max((int) $lease->due_day, 1), $periodFrom->daysInMonth);

        return $periodFrom->copy()->day($day);
    }

    private function effectiveStart(Lease $lease, LeaseChargeRule $rule): Carbon
    {
        $leaseStart = Carbon::parse($lease->billing_start_date)->startOfDay();
        $ruleStart = Carbon::parse($rule->effective_from)->startOfDay();

        return $leaseStart->greaterThan($ruleStart) ? $leaseStart : $ruleStart;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periodRange(CarbonInterface $periodFrom, LeaseChargeRule $rule): array
    {
        $periodFrom = Carbon::parse($periodFrom)->startOfDay();
        $periodTo = $this->nextPeriodStart($periodFrom, $rule)->subDay()->endOfDay();

        return [$periodFrom, $periodTo];
    }

    private function nextPeriodStart(CarbonInterface $periodFrom, LeaseChargeRule $rule): Carbon
    {
        $periodFrom = Carbon::parse($periodFrom)->startOfDay();
        $intervalCount = max((int) $rule->interval_count, 1);

        return match ($rule->frequency) {
            ChargeFrequency::Monthly => $periodFrom->copy()->addMonthsNoOverflow($intervalCount),
            ChargeFrequency::Yearly => $periodFrom->copy()->addYearsNoOverflow($intervalCount),
            ChargeFrequency::CustomInterval => $this->applyCustomInterval($periodFrom->copy(), $rule->interval_unit, $intervalCount),
        };
    }

    private function applyCustomInterval(Carbon $date, ?ChargeIntervalUnit $intervalUnit, int $intervalCount): Carbon
    {
        return match ($intervalUnit) {
            ChargeIntervalUnit::Day => $date->addDays($intervalCount),
            ChargeIntervalUnit::Week => $date->addWeeks($intervalCount),
            ChargeIntervalUnit::Month => $date->addMonthsNoOverflow($intervalCount),
            ChargeIntervalUnit::Year => $date->addYearsNoOverflow($intervalCount),
            null => $date->addMonthsNoOverflow($intervalCount),
        };
    }
}
