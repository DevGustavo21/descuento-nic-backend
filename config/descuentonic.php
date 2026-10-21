<?php

return [
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),

    'free_email_domains' => [
        'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.es', 'hotmail.com',
        'hotmail.es', 'outlook.com', 'outlook.es', 'live.com', 'msn.com',
        'icloud.com', 'me.com', 'mac.com', 'aol.com', 'proton.me', 'protonmail.com',
        'pm.me', 'zoho.com', 'yandex.com', 'gmx.com', 'gmx.es', 'mail.com',
        'inbox.com', 'tutanota.com', 'tuta.com', 'fastmail.com', 'hey.com',
        'mailinator.com', '10minutemail.com', 'guerrillamail.com', 'tempmail.com',
    ],

    'departments' => [
        'Managua', 'León', 'Granada', 'Masaya', 'Estelí', 'Chinandega',
        'Matagalpa', 'Rivas', 'Jinotega', 'Carazo', 'Chontales', 'Boaco',
        'Madriz', 'Nueva Segovia', 'Río San Juan', 'RACCS', 'RACCN',
    ],

    'channels' => [
        'store' => 'En tienda física',
        'delivery' => 'Delivery',
        'online' => 'Online',
        'whatsapp' => 'WhatsApp',
    ],

    'payment_methods' => [
        'cash' => 'Efectivo',
        'card' => 'Tarjeta',
        'transfer' => 'Transferencia',
    ],
];
