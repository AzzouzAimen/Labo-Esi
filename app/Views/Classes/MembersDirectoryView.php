<?php
/**
 * MembersDirectoryView Class
 * Renders the members directory page with search and filters
 */
class MembersDirectoryView extends View {

    /**
     * Render the members directory page
     */
    public function render() {
        // Start output buffering
        ob_start();
        
        // Include the members directory template
        $this->template('members_directory');
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Load the layout with this content
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
