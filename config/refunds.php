<?php

return [
    // Number of days from sale date within which refunds are allowed.
    // Default is 7 (business rule requested).
    'window_days' => env('REFUND_WINDOW_DAYS', 7),
];

