<?php
// Sørg for at ROOT_PATH er defineret
if (!defined('ROOT_PATH')) {
    exit('ROOT_PATH not defined.');
}

// FILESYSTEM PATHS (Til PHP)
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('KERNEL_PATH',  ROOT_PATH . '/kernel');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('ASSETS_PATH',  ROOT_PATH . '/assets');
define('UPLOAD_PATH',  ASSETS_PATH . '/uploads'); 
define('LOG_PATH',     ROOT_PATH . '/logs');

// URL PATHS
define('ASSETS_DIR_NAME', 'assets');
define('UPLOADS_DIR_NAME', 'uploads');
define('ASSETS_URI', '/' . ASSETS_DIR_NAME);
define('UPLOADS_URI', ASSETS_URI . '/' . UPLOADS_DIR_NAME);