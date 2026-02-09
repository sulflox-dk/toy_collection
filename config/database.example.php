<?php
return [
    'db_host'     => 'localhost',
    'db_name'     => 'your_database_name',
    'db_user'     => 'your_username',
    'db_pass'     => 'your_password',
    'db_charset'  => 'utf8mb4',
    'db_options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];