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
    'shortLinkEnable' => getenv('SHORT_LINK_ENABLE'),
    'manticore' => [
        'host' => 'lib-manticore',
        'port' => 9308,
        'max_matches' => getenv('MANTICORE_MAX_MATCHES') ?? 0, // Maximum amount of matches that the server keeps in RAM for each table and can return to the client. Default is unlimited.
    ],
    'searchResults' => [
        'pageSize' => (int)getenv('PAGE_SIZE'),
    ],
    'indexes' => [
        'common' =>  getenv('MANTICORE_DB_NAME_COMMON'),
        'concept' => 'vpsssr_library_concept',
    ],
    'bsVersion' => '5.x'
];
