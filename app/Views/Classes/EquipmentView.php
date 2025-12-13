<?php
/**
 * EquipmentView Class
 */
class EquipmentView extends View {
    public function render() {
        ob_start();
        $this->template('equipment_list');
        $content = ob_get_clean();
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
