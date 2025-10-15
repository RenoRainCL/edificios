<?php
// 📁 config/security.php
return [
    'headers' => [
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;"
    ],
    
    'session' => [
        'name' => 'secure_edificios_session',
        'cookie_httponly' => true,
        'cookie_secure' => true,
        'cookie_samesite' => 'Strict',
        'gc_maxlifetime' => 3600
    ],
    
    'rate_limiting' => [
        'login' => [
            'attempts' => 5,
            'window' => 900 // 15 minutos
        ],
        'api' => [
            'requests' => 100,
            'window' => 60 // 1 minuto
        ]
    ]
];
?>