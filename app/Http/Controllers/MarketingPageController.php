<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\View\View;

class MarketingPageController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', $this->sharedData([
            'title' => __('marketing.meta.home_title'),
            'activePage' => 'home',
        ]));
    }

    public function pricing(): View
    {
        return view('marketing.pricing', $this->sharedData([
            'title' => __('marketing.meta.pricing_title'),
            'activePage' => 'pricing',
        ]));
    }

    public function contact(): View
    {
        return view('marketing.contact', $this->sharedData([
            'title' => __('marketing.meta.contact_title'),
            'activePage' => 'contact',
        ]));
    }

    public function privacy(): View
    {
        return view('marketing.privacy', $this->sharedData([
            'title' => __('marketing.meta.privacy_title'),
            'activePage' => 'privacy',
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sharedData(array $data = []): array
    {
        return [
            ...$data,
            'plans' => SubscriptionPlan::query()
                ->where('is_active', true)
                ->where('is_public', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ];
    }
}
