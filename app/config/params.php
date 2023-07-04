<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'cookieDomain' => getenv('COOKIE_DOMAIN'),
    'frontendHostInfo' => getenv('FRONTEND_URL'),
    'githubRepositoryUrl' => getenv('GH_REPO_URL'),
    'urlShortenerHost' => getenv('URL_SHORTENER_HOST'), // Хост в сети интернет, в локальной сети docker - это наименования сервиса
    'urlShortenerUrl' => getenv('URL_SHORTENER_URL'), // Хост в сети интернет, в локальной сети docker - это наименования сервиса
    'manticore' => [
        'host' => 'manticore',
        'port' => 9308
    ],
    'searchResults' => [
        'pageSize' => (int)getenv('PAGE_SIZE'),
    ],
    'indexes' => [
        'common' =>  getenv('MANTICORE_DB_NAME_COMMON'),
        'concept' => 'vpsssr_library_concept',
    ]
];
