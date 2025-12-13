<?php
/**
 * DashboardView Class
 * Renders the user dashboard
 */
class DashboardView extends View {

    /**
     * Render the dashboard
     */
    public function render() {
        // Start output buffering
        ob_start();
        
        // Include the dashboard template
        $this->template('dashboard');
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Load the layout with this content
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
