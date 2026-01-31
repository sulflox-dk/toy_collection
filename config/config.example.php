<?php
return [
    // Real file location config/config.php
    // Database settings
    'db_host' => 'localhost',
    'db_name' => 'db_name',
    'db_user' => 'db_user',
    'db_pass' => 'password',
    'db_charset' => 'utf8mb4',

    // System Settings
    'base_url'   => 'base/url', 
    'debug_mode' => true,

    // Sti på serveren (relativt til der hvor index.php kører)
    'upload_path' => 'assets/uploads/', 
    // Mappe-navn til URL (bruges sammen med base_url)
    'upload_dir'  => 'assets/uploads/',

];