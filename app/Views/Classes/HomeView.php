<?php
/**
 * HomeView Class
 * Renders the homepage with slideshow and news
 */
class HomeView extends View {

    /**
     * Render the homepage
     */
    public function render() {
        // Start output buffering to capture the content
        ob_start();
        
        // Include the home template
        $this->template('home');
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Load the layout with this content
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
