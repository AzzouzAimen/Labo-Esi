<?php
/**
 * Base View Class
 * All view classes extend this class
 */
abstract class View {
    protected $data;
    protected $lang;
    protected $templatePath;

    /**
     * Constructor
     * @param array $data Data to display
     * @param array $lang Language strings
     */
    public function __construct($data = [], $lang = []) {
        $this->data = $data;
        $this->lang = $lang;
    }

    /**
     * Render the view
     * Must be implemented by child classes
     */
    abstract public function render();

    /**
     * Include a template file
     * @param string $templateName Template file name (without .phtml)
     * @param array $templateData Additional data for this template
     */
    protected function template($templateName, $templateData = []) {
        $templateFile = BASE_PATH . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . $templateName . '.phtml';
        
        if (!file_exists($templateFile)) {
            die("Template not found: $templateName");
        }
        
        // Extract data and lang to make them available in template
        extract($this->data);
        extract($templateData);
        $lang = $this->lang;
        
        include $templateFile;
    }

    /**
     * Escape HTML output
     * @param string $string
     * @return string
     */
    protected function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get a language string
     * @param string $key
     * @param string $default
     * @return string
     */
    protected function text($key, $default = '') {
        return $this->lang[$key] ?? $default;
    }
}
