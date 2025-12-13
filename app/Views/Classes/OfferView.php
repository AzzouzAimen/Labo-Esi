<?php
/**
 * OfferView Class
 */
class OfferView extends View {
    public function render() {
        ob_start();
        $this->template('offers');
        $content = ob_get_clean();
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
