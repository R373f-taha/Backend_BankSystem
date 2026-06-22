<?php

return [
    'api_key' => env('GEMINI_API_KEY'),

    // تغيير base_url لإزالة v1beta
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1'),

    // استخدام نموذج مدعوم
$models = [
    'gemini-2.0-flash-exp',        // النسخة التجريبية من Gemini 2.0 Flash
    'gemini-2.0-flash',             // الإصدار المستقر من Gemini 2.0 Flash
    'gemini-1.5-flash',             // Gemini 1.5 Flash (يعمل دائماً)
    'gemini-1.5-pro',               // Gemini 1.5 Pro
],

    'timeout' => env('GEMINI_TIMEOUT', 120),

    'retry' => [
        'times' => env('GEMINI_RETRY_TIMES', 0),
        'sleep' => env('GEMINI_RETRY_SLEEP', 100),
    ],

    'generation' => [
        'temperature' => env('GEMINI_TEMPERATURE', 0.3),
        'max_output_tokens' => env('GEMINI_MAX_OUTPUT_TOKENS', 1024),
        'top_p' => env('GEMINI_TOP_P', 0.95),
        'top_k' => env('GEMINI_TOP_K', 40),
    ],

    // أضف هذا الجزء لحل مشكلة SSL
    'guzzle' => [
        'timeout' => 120,
        'connect_timeout' => 60,
        'verify' => false, // فقط للتطوير
    ],
];
