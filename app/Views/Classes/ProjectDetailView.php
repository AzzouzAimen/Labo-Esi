<?php
/**
 * ProjectDetailView Class
 * Renders a single project's details
 */
class ProjectDetailView extends View {

    /**
     * Render the project detail page
     */
    public function render() {
        // Start output buffering
        ob_start();
        
        // Include the project detail template
        $this->template('project_detail');
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Load the layout with this content
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
