<?php
/**
 * OfferController
 * Handles "Offres et opportunités" page
 */
class OfferController extends Controller {
    public function index() {
        $lang = $this->loadLang('fr');

        $data = [
            'pageTitle' => $lang['offers_title'] ?? 'Offres et opportunités'
        ];

        $this->view('Offer', $data, $lang);
    }
}
