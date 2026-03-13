<?php

namespace App\Http\Controllers\Client;

use App\Enums\LeaseStatus;
use App\Http\Controllers\Controller;
use App\Models\TenantProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantLeaseController extends Controller
{
    public function index(Request $request): View
    {
        /** @var TenantProfile|null $tenantProfile */
        $tenantProfile = $request->user()->tenantProfile;

        return view('client.tenant.leases.index', [
            'tenantProfile' => $tenantProfile,
            'leases' => $tenantProfile?->leases()
                ->with(['propertyUnit.property', 'chargeRules'])
                ->orderByRaw('case when status = ? then 0 else 1 end', [LeaseStatus::Active->value])
                ->orderByDesc('start_date')
                ->get() ?? collect(),
        ]);
    }
}
