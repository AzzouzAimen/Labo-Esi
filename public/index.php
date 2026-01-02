<?php
/**
 * Front Controller - Entry Point
 * All requests are routed through this file
 */

// Start session
session_start();

// Load configuration
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'config.php';

// Load core classes
require_once BASE_PATH . 'core' . DIRECTORY_SEPARATOR . 'Database.php';
require_once BASE_PATH . 'core' . DIRECTORY_SEPARATOR . 'Model.php';
require_once BASE_PATH . 'core' . DIRECTORY_SEPARATOR . 'Router.php';
require_once BASE_PATH . 'core' . DIRECTORY_SEPARATOR . 'Controller.php';
require_once BASE_PATH . 'core' . DIRECTORY_SEPARATOR . 'View.php';
require_once BASE_PATH . 'core' . DIRECTORY_SEPARATOR . 'Component.php';

// Load LayoutView class (required by all view classes)
$layoutViewPath = BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'LayoutView.php';
if (!file_exists($layoutViewPath)) {
    die('LayoutView.php not found at: ' . $layoutViewPath);
}
require_once $layoutViewPath;

// Load all view classes
require_once BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'HomeView.php';
require_once BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'ContactView.php';
require_once BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'ProjectView.php';
require_once BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'ProjectDetailView.php';
require_once BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PublicationView.php';
require_once BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'EquipmentView.php';
require_once BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'TeamView.php';
require_once BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'MemberProfileView.php';
require_once BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'LoginView.php';
require_once BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'DashboardView.php';

// Autoload all components
$componentsPath = BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR;
if (is_dir($componentsPath)) {
    foreach (glob($componentsPath . '*.php') as $componentFile) {
        require_once $componentFile;
    }
}

// Create router and dispatch request
$router = new Router();
$router->dispatch();
