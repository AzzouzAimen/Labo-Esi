<?php
/**
 * PublicationView Class
 */
class PublicationView extends View {
    public function render() {
        ob_start();
        $this->template('publication_list');
        $content = ob_get_clean();
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
