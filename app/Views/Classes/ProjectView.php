<?php
/**
 * ProjectView Class
 * Renders the project catalog with filtering
 */
class ProjectView extends View {

    /**
     * Render the project catalog
     */
    public function render() {
        // Start output buffering
        ob_start();
        
        // Include the project list template
        $this->template('project_list');
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Load the layout with this content
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
