<?php
/**
 * Base Component Class
 * 
 * Abstract class that serves as the foundation for all UI components.
 * Provides a standardized way to pass properties and render HTML.
 */
abstract class Component {
    /**
     * @var array Component properties/configuration
     */
    protected $props;

    /**
     * Constructor
     * 
     * @param array $props Optional array of properties to pass to the component
     */
    public function __construct(array $props = []) {
        $this->props = $props;
    }

    /**
     * Render the component HTML
     * 
     * @return string The rendered HTML output
     */
    abstract public function render(): string;

    /**
     * Magic method to allow echo/print of component
     * 
     * @return string The rendered HTML output
     */
    public function __toString() {
        return $this->render();
    }
}
