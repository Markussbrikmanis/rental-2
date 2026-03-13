<?php

namespace App\Http\Controllers\Client;

use App\Enums\LeaseStatus;
use App\Enums\MeterReadingSource;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Meter;
use App\Models\TenantProfile;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantMeterController extends Controller
{
    public function index(Request $request): View
    {
        /** @var TenantProfile|null $tenantProfile */
        $tenantProfile = $request->user()->tenantProfile;

        return view('client.tenant.meters.index', [
            'tenantProfile' => $tenantProfile,
            'leases' => $tenantProfile
                ? Lease::query()
                    ->where('tenant_profile_id', $tenantProfile->id)
                    ->where('status', LeaseStatus::Active->value)
                    ->with([
                        'propertyUnit.property',
                        'propertyUnit.meters' => fn ($query) => $query->where('is_active', true)->orderBy('name'),
                        'propertyUnit.meters.readings' => fn ($query) => $query->latest('reading_date'),
                    ])
                    ->orderBy('start_date')
                    ->get()
                : collect(),
        ]);
    }

    public function store(Request $request, Meter $meter): RedirectResponse
    {
        $this->ensureAccessibleMeter($request, $meter);

        $today = Carbon::today();
        $earliestAllowedDate = $today->copy()->subDays(3);

        $validated = $request->validate(
            [
                'reading_date' => ['required', 'date', 'after_or_equal:'.$earliestAllowedDate->toDateString(), 'before_or_equal:'.$today->toDateString()],
                'value' => ['required', 'numeric', 'min:0'],
                'notes' => ['nullable', 'string'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $meter->readings()->create([
            ...$validated,
            'source' => MeterReadingSource::Manual,
        ]);

        return redirect()
            ->route('client.tenant-meters.index')
            ->with('status', __('app.rental.meter_readings.messages.recorded'));
    }

    private function ensureAccessibleMeter(Request $request, Meter $meter): void
    {
        $tenantProfileId = $request->user()->tenantProfile?->id;

        abort_unless(
            $tenantProfileId !== null
                && $meter->is_active
                && Lease::query()
                    ->where('tenant_profile_id', $tenantProfileId)
                    ->where('status', LeaseStatus::Active->value)
                    ->where('property_unit_id', $meter->property_unit_id)
                    ->exists(),
            404,
        );
    }
}
