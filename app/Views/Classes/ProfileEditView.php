<?php
/**
 * ProfileEditView Class
 * Renders the profile edit form
 */
class ProfileEditView extends View {
    public function render() {
        ob_start();
        $this->template('profile_edit');
        $content = ob_get_clean();
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
