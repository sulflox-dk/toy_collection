<?php
// kernel/Controller.php
namespace CollectionApp\Kernel;

class Controller {
    protected $view;
    protected $db;

    public function __construct() {
        $this->view = new Template();
        
        // FØR: $this->db = Database::getInstance()->getConnection();
        // NU: Vi gemmer indstansen af vores egen klasse
        $this->db = Database::getInstance();
    }
}