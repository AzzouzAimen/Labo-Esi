<?php
/**
 * PublicationController
 * Handles publication listing and details
 */
class PublicationController extends Controller {

    /**
     * Publications listing page
     */
    public function index() {
        $lang = $this->loadLang('fr');
        
        $data = [
            'pageTitle' => $lang['publications_title']
        ];
        
        // For now, show a simple message
        $this->view('Publication', $data, $lang);
    }
}
