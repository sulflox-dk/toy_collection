<?php
namespace CollectionApp\Kernel;

class Template {
    
    protected $layout = 'main'; // Standard layout fil (views/layouts/main.php)

    /**
     * Sæt et andet layout (f.eks. 'auth' til login sider)
     */
    public function setLayout($layoutName) {
        $this->layout = $layoutName;
    }

    /**
     * Renderer et view pakket ind i layoutet
     * @param string $viewName  Navnet på filen (f.eks. 'list', 'add')
     * @param array  $data      Data der skal sendes til viewet
     * @param string $module    Modul navnet (f.eks. 'Inventory') hvis viewet ligger i et modul
     */
    public function render($viewName, $data = [], $module = null) {
        // 1. Pak data ud så $data['toys'] bliver til variablen $toys
        extract($data);

        // 2. Buffer modulets specifikke indhold
        ob_start();
        
        $viewPath = $this->findViewPath($viewName, $module);
        
        if ($viewPath) {
            require $viewPath;
        } else {
            echo "<div class='alert alert-danger'>Fejl: View filen '$viewName' blev ikke fundet.</div>";
            // Debug info hvis i debug mode
            if (Config::get('debug_mode')) {
                echo "<small>Leder efter: " . ($module ? "modules/$module/Views/$viewName.php" : "views/$viewName.php") . "</small>";
            }
        }
        
        // Denne variabel $content bliver nu tilgængelig i layout filen
        $content = ob_get_clean();

        // 3. Render det globale Layout (som udskriver $content)
        $layoutPath = ROOT_PATH . '/views/layouts/' . $this->layout . '.php';
        
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo "Fejl: Layout filen '{$this->layout}' blev ikke fundet i views/layouts/.";
        }
    }

    /**
     * Hjælper til at finde hvor view filen ligger
     */
    private function findViewPath($viewName, $module) {
        // Mulighed A: Kig inde i det specifikke Modul
        if ($module) {
            // e.g. modules/Collection/Views/list.php
            $path = ROOT_PATH . "/modules/$module/Views/$viewName.php";
            if (file_exists($path)) return $path;
        }

        // Mulighed B: Kig i Globale Views (f.eks. dashboard/index.php eller errors/404.php)
        // Vi tjekker om viewName allerede indeholder en sti (f.eks. 'errors/404')
        $path = ROOT_PATH . "/views/$viewName.php";
        if (file_exists($path)) return $path;

        return false;
    }

    /**
     * Renderer KUN view-filen uden layout (til AJAX/Modals)
     */
    public function renderPartial($viewName, $data = [], $module = null) {
        extract($data);
        
        $viewPath = $this->findViewPath($viewName, $module);
        
        if ($viewPath) {
            require $viewPath;
        } else {
            echo "Error: View '$viewName' not found.";
        }
    }
}