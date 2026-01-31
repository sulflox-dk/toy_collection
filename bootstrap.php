<?php
// bootstrap.php
session_start();

define('ROOT_PATH', __DIR__);

// 1. Load Autoloader
require_once ROOT_PATH . '/kernel/Autoloader.php';
CollectionApp\Kernel\Autoloader::register();

use CollectionApp\Kernel\Config;
use CollectionApp\Kernel\Debugger;

// 2. Load Config
Config::load(ROOT_PATH . '/config/config.php');

// 3. Start Debugger (Den læser selv debug_mode fra Config)
Debugger::init();