<?php
/**
 * LayoutView Class
 * Renders the main layout wrapper (header, nav, footer)
 */
class LayoutView extends View {
    private $contentHtml;

    /**
     * Constructor
     * @param string $contentHtml The inner HTML content
     * @param array $lang Language strings
     */
    public function __construct($contentHtml, $lang = []) {
        parent::__construct([], $lang);
        $this->contentHtml = $contentHtml;
    }

    /**
     * Render the layout with content
     */
    public function render() {
        // Pass the inner page HTML to the layout template so it can be displayed
        $this->template('layout', ['content' => $this->contentHtml]);
    }
}
