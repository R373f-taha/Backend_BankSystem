<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Approval Threshold
    |--------------------------------------------------------------------------
    |
    | Transactions (transfers & withdrawals) above this amount require
    | employee approval before execution.
    |
    */
    'approval_threshold' => env('APPROVAL_THRESHOLD', 10000),
];
