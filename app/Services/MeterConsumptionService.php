<?php

namespace App\Services;

use App\Models\Meter;
use App\Models\MeterReading;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class MeterConsumptionService
{
    /**
     * @return Collection<int, array{reading: MeterReading, previous: ?MeterReading, consumption: ?float}>
     */
    public function readingDeltas(Meter $meter): Collection
    {
        $previous = null;

        return $meter->readings()
            ->orderBy('reading_date')
            ->get()
            ->map(function (MeterReading $reading) use (&$previous): array {
                $consumption = $previous ? (float) $reading->value - (float) $previous->value : null;

                $result = [
                    'reading' => $reading,
                    'previous' => $previous,
                    'consumption' => $consumption,
                ];

                $previous = $reading;

                return $result;
            });
    }

    public function latestConsumption(Meter $meter): ?float
    {
        return $this->readingDeltas($meter)->last()['consumption'] ?? null;
    }

    public function consumptionForPeriod(Meter $meter, CarbonInterface $periodFrom, CarbonInterface $periodTo): ?float
    {
        $startReading = $meter->readings()
            ->whereDate('reading_date', '<=', $periodFrom)
            ->orderByDesc('reading_date')
            ->first();

        if (! $startReading) {
            $startReading = $meter->readings()
                ->whereDate('reading_date', '>=', $periodFrom)
                ->whereDate('reading_date', '<=', $periodTo)
                ->orderBy('reading_date')
                ->first();
        }

        $endReading = $meter->readings()
            ->whereDate('reading_date', '<=', $periodTo)
            ->orderByDesc('reading_date')
            ->first();

        if (! $startReading || ! $endReading || $startReading->id === $endReading->id) {
            return null;
        }

        $consumption = (float) $endReading->value - (float) $startReading->value;

        return $consumption > 0 ? $consumption : null;
    }
}
