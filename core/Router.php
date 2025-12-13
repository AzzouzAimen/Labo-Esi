<?php
/**
 * Router Class
 * Parses URL and dispatches to appropriate Controller
 * Pattern: index.php?controller=Home&action=index
 */
class Router {
    private $controller = 'Home';
    private $action = 'index';
    private $params = [];

    /**
     * Parse the URL and extract controller, action, and parameters
     */
    public function __construct() {
        $this->parseUrl();
    }

    /**
     * Parse URL parameters from $_GET
     */
    private function parseUrl() {
        // Get controller from URL (default: Home)
        if (isset($_GET['controller'])) {
            $this->controller = ucfirst($_GET['controller']);
        }

        // Get action from URL (default: index)
        if (isset($_GET['action'])) {
            $this->action = $_GET['action'];
        }

        // Get additional parameters
        $this->params = $_GET;
        unset($this->params['controller']);
        unset($this->params['action']);
    }

    /**
     * Dispatch the request to the appropriate controller and action
     */
    public function dispatch() {
        // Build controller class name
        $controllerClass = $this->controller . 'Controller';
        $controllerFile = BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . $controllerClass . '.php';

        // Check if controller file exists
        if (!file_exists($controllerFile)) {
            die("Controller not found: $controllerClass");
        }

        // Load the controller
        require_once $controllerFile;

        // Check if controller class exists
        if (!class_exists($controllerClass)) {
            die("Controller class not found: $controllerClass");
        }

        // Instantiate the controller
        $controller = new $controllerClass();

        // Check if action method exists
        if (!method_exists($controller, $this->action)) {
            die("Action not found: {$this->action} in $controllerClass");
        }

        // Call the action with parameters
        // Convert associative array to indexed array for PHP 8+ compatibility
        call_user_func_array([$controller, $this->action], array_values($this->params));
    }

    /**
     * Get current controller name
     * @return string
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * Get current action name
     * @return string
     */
    public function getAction() {
        return $this->action;
    }
}
