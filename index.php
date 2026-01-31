<?php
// index.php
require_once 'bootstrap.php';

use CollectionApp\Kernel\Router;
use CollectionApp\Kernel\Debugger;

// Kør applikationen
$router = new Router();
$router->handleRequest();

// Vis debugger baren i bunden (hvis enabled i config)
Debugger::render();