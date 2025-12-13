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

        $equipModel = $this->model('EquipmentModel');

        $filters = [
            'category' => $_GET['category'] ?? 'all',
            'status' => $_GET['status'] ?? 'all',
            'q' => trim($_GET['q'] ?? '')
        ];

        $equipment = $equipModel->getAllEquipment($filters);
        $stats = $equipModel->getUsageStats(5);

        $data = [
            'pageTitle' => $lang['equipment_title'],
            'equipment' => $equipment ?: [],
            'categories' => $equipModel->getCategories(),
            'filters' => $filters,
            'stats' => $stats ?: [],
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null
        ];

        $this->view('Equipment', $data, $lang);
    }

    /**
     * Reservation submission
     */
    public function reserve() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('Auth', 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('Equipment', 'index');
            return;
        }

        $equipId = $_POST['equip_id'] ?? null;
        $start = $_POST['date_debut'] ?? null;
        $end = $_POST['date_fin'] ?? null;
        $motif = $_POST['motif'] ?? '';

        if (!$equipId || !$start || !$end) {
            $this->redirect('Equipment', 'index', ['error' => 'Veuillez renseigner toutes les informations de réservation.']);
            return;
        }

        $equipModel = $this->model('EquipmentModel');
        $result = $equipModel->createReservation((int)$_SESSION['user_id'], (int)$equipId, $start, $end, $motif);

        if (!empty($result['success'])) {
            $this->redirect('Equipment', 'index', ['success' => 'Réservation enregistrée.']);
        }

        $this->redirect('Equipment', 'index', ['error' => $result['error'] ?? 'Erreur lors de la réservation.']);
    }
}
