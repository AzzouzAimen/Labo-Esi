<?php
/**
 * ProjectEditView Class
 * Renders the project edit form for project managers
 */
class ProjectEditView extends View {
    public function render() {
        ob_start();
        $this->template('project_edit');
        $content = ob_get_clean();
        $layout = new LayoutView($content, $this->lang);
        $layout->render();
    }
}
