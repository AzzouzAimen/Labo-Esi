<?php
/**
 * TeamView Class
 * Renders the team listing with organizational structure
 */
class TeamView extends View {

    /**
     * Render the teams page
     */
    public function render() {
        // Start output buffering
        ob_start();
        
        // Include the team list template
        $this->template('team_list');
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Load the layout with this content
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
