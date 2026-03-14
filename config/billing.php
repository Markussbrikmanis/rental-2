<?php

return [
    'owner_trial_enabled' => (bool) env('OWNER_TRIAL_ENABLED', true),
    'owner_trial_days' => (int) env('OWNER_TRIAL_DAYS', 14),
    'owner_plans' => [
        'starter' => [
            'property_limit' => (int) env('OWNER_PLAN_STARTER_PROPERTY_LIMIT', 3),
        ],
        'growth' => [
            'property_limit' => (int) env('OWNER_PLAN_GROWTH_PROPERTY_LIMIT', 10),
        ],
        'scale' => [
            'property_limit' => (int) env('OWNER_PLAN_SCALE_PROPERTY_LIMIT', 30),
        ],
    ],
];
