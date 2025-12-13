<?php
/**
 * EventDetailView Class
 */
class EventDetailView extends View {
    public function render() {
        ob_start();
        $this->template('event_detail');
        $content = ob_get_clean();
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
