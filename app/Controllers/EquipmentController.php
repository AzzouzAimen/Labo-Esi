<?php
/**
 * EquipmentController
 * Handles equipment listing and reservations
 */
class EquipmentController extends Controller {

    /**
     * Equipment listing page
     */
    public function index() {
        $lang = $this->loadLang('fr');
        
        $data = [
            'pageTitle' => $lang['equipment_title']
        ];
        
        // For now, show a simple message
        $this->view('Equipment', $data, $lang);
    }
}
