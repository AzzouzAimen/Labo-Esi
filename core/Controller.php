<?php
/**
 * Base Controller Class
 * All controllers extend this class
 */
abstract class Controller {
    
    /**
     * Load a model
     * @param string $modelName
     * @return object Model instance
     */
    protected function model($modelName) {
        $modelFile = BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $modelName . '.php';
        
        if (!file_exists($modelFile)) {
            die("Model not found: $modelName");
        }
        
        require_once $modelFile;
        
        if (!class_exists($modelName)) {
            die("Model class not found: $modelName");
        }
        
        return new $modelName();
    }

    /**
     * Load a view
     * @param string $viewName Name of the view class (without 'View' suffix)
     * @param array $data Data to pass to the view
     * @param array $lang Language strings
     */
    protected function view($viewName, $data = [], $lang = []) {
        $viewClass = $viewName . 'View';
        $viewFile = BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . $viewClass . '.php';
        
        if (!file_exists($viewFile)) {
            die("View class not found: $viewClass");
        }
        
        require_once $viewFile;
        
        if (!class_exists($viewClass)) {
            die("View class not found: $viewClass");
        }
        
        $view = new $viewClass($data, $lang);
        $view->render();
    }

    /**
     * Load language file
     * @param string $langCode Language code (default: fr)
     * @return array Language strings
     */
    protected function loadLang($langCode = 'fr') {
        $langFile = BASE_PATH . 'lang' . DIRECTORY_SEPARATOR . $langCode . '.php';
        
        if (!file_exists($langFile)) {
            return [];
        }
        
        return require $langFile;
    }

    /**
     * Redirect to another page
     * @param string $controller
     * @param string $action
     * @param array $params
     */
    protected function redirect($controller = 'Home', $action = 'index', $params = []) {
        $url = BASE_URL . 'index.php?controller=' . $controller . '&action=' . $action;
        
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        
        header('Location: ' . $url);
        exit;
    }

    /**
     * Return JSON response (for AJAX)
     * @param mixed $data
     * @param int $statusCode
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
