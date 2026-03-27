<?php

return [
    'provider' => env('AI_PROVIDER', 'mock'),

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest'),
        'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 1200),
        'timeout_seconds' => (int) env('ANTHROPIC_TIMEOUT_SECONDS', 20),
    ],

    'limits' => [
        'daily_quota' => (int) env('AI_DAILY_QUOTA', 100),
        'per_minute' => (int) env('AI_RATE_LIMIT_PER_MINUTE', 20),
    ],

    'logging' => [
        'max_prompt_chars' => (int) env('AI_LOG_MAX_PROMPT_CHARS', 5000),
        'max_response_chars' => (int) env('AI_LOG_MAX_RESPONSE_CHARS', 5000),
    ],
];
