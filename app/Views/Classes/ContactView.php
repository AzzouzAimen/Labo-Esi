<?php
/**
 * ContactView Class
 */
class ContactView extends View {
    public function render() {
        ob_start();
        $this->template('contact');
        $content = ob_get_clean();
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
