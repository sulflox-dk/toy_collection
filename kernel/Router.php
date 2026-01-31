<?php
namespace CollectionApp\Kernel;

class Router {
    public function handleRequest() {
        // 1. Get Module (Default: Dashboard)
        $module = isset($_GET['module']) ? ucfirst($_GET['module']) : 'Dashboard';
        
        // 2. Get Controller (Default: Samme navn som modulet)
        // HVIS ?controller=Item er sat, brug ItemController. ELLERs brug Modulnavnet (DashboardController)
        $controllerName = isset($_GET['controller']) ? ucfirst($_GET['controller']) : $module;
        
        // 3. Get Action (Default: index)
        $action = isset($_GET['action']) ? $_GET['action'] : 'index';

        // 4. Construct Class Name
        // Fx: CollectionApp\Modules\Collection\Controllers\ItemController
        $controllerClass = "CollectionApp\\Modules\\$module\\Controllers\\{$controllerName}Controller";

        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            
            if (method_exists($controller, $action)) {
                $controller->$action();
            } else {
                echo "Error: Action '$action' not found in $controllerName.";
            }
        } else {
            // Fallback fejlhåndtering
            echo "Error: Controller class '$controllerClass' not found.";
            if (Config::get('debug_mode')) {
                echo "<br>Module: $module<br>Controller: $controllerName";
            }
        }
    }
}