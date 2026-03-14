<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerSubscriptionIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isOwner()) {
            return $next($request);
        }

        if ($request->routeIs('client.billing.*') || $request->routeIs('client.logout')) {
            return $next($request);
        }

        if ($user->ownerHasBillingAccess()) {
            return $next($request);
        }

        return redirect()
            ->route('client.billing.index')
            ->with('error', __('app.subscription.messages.inactive_redirect'));
    }
}
