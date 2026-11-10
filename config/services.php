<?php

return [
    // Chatbot AI (opsional). Tanpa API key, chatbot memakai mode dasar berbasis aturan.
    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-haiku-5-5'),
        'version' => '2023-06-01',
        'timeout' => 30,
    ],
];
