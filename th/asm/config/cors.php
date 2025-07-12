<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Bỏ '*' ở đây để cụ thể hơn, nếu không có endpoint nào khác ngoài API cần CORS
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5174', 'https://localhost:5174'], // PHẢI khớp chính xác với URL frontend
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];