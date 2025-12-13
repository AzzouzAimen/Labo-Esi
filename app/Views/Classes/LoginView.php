<?php
/**
 * LoginView Class
 * Renders the login form
 */
class LoginView extends View {

    /**
     * Render the login page
     */
    public function render() {
        // Start output buffering
        ob_start();
        
        // Include the login template
        $this->template('login');
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Load the layout with this content
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
