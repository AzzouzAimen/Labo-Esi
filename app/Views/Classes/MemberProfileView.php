<?php
/**
 * MemberProfileView Class
 * Renders a member's profile page
 */
class MemberProfileView extends View {

    /**
     * Render the member profile
     */
    public function render() {
        // Start output buffering
        ob_start();
        
        // Include the member profile template
        $this->template('member_profile');
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Load the layout with this content
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
