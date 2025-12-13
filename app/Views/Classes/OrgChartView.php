<?php
/**
 * OrgChartView Class
 * Renders the organizational chart
 */
class OrgChartView extends View {
    public function render() {
        ob_start();
        $this->template('org_chart');
        $content = ob_get_clean();
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
