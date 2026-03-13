<?php

namespace App\Http\Controllers\Client;

use App\Enums\MeterReadingSource;
use App\Http\Controllers\Controller;
use App\Models\Meter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MeterReadingController extends Controller
{
    public function store(Request $request, Meter $meter): RedirectResponse
    {
        $this->ensureOwnedMeter($request, $meter);

        $meter->readings()->create(
            $request->validate(
                [
                    'reading_date' => ['required', 'date'],
                    'value' => ['required', 'numeric', 'min:0'],
                    'source' => ['required', Rule::in(MeterReadingSource::values())],
                    'notes' => ['nullable', 'string'],
                ],
                trans('app.validation.messages'),
                trans('app.validation.attributes'),
            ),
        );

        return redirect()
            ->route('client.meters.show', $meter)
            ->with('status', __('app.rental.meter_readings.messages.recorded'));
    }

    private function ensureOwnedMeter(Request $request, Meter $meter): void
    {
        abort_unless($meter->propertyUnit->property->user_id === $request->user()->id, 404);
    }
}
