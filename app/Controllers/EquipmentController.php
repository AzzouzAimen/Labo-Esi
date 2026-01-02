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
        $isUrgent = isset($_POST['is_urgent']) && $_POST['is_urgent'] == '1';
        $urgentReason = $_POST['urgent_reason'] ?? '';

        if (!$equipId || !$start || !$end) {
            $this->redirect('Equipment', 'index', ['error' => 'Veuillez renseigner toutes les informations de réservation.']);
            return;
        }

        // Validate urgent reason if urgent checkbox is checked
        if ($isUrgent && empty(trim($urgentReason))) {
            $this->redirect('Equipment', 'index', ['error' => 'Veuillez préciser la raison de l\'urgence.']);
            return;
        }

        $equipModel = $this->model('EquipmentModel');
        $result = $equipModel->createReservation(
            (int)$_SESSION['user_id'], 
            (int)$equipId, 
            $start, 
            $end, 
            $motif,
            $isUrgent,
            $urgentReason
        );

        if (!empty($result['success'])) {
            $message = 'Réservation enregistrée.';
            // Check if conflicts were detected
            if (!empty($result['reservation_id'])) {
                // Could optionally check conflict status here
                $message = 'Réservation enregistrée. Vérifiez le planning pour d\'éventuels conflits.';
            }
            $this->redirect('Equipment', 'index', ['success' => $message]);
        }

        $this->redirect('Equipment', 'index', ['error' => $result['error'] ?? 'Erreur lors de la réservation.']);
    }

    /**
     * Get reservations for an equipment (AJAX endpoint)
     * Returns JSON data for the ReservationHistoryModal
     */
    public function getReservations() {
        header('Content-Type: application/json');

        $equipId = $_GET['equip_id'] ?? null;
        if (!$equipId) {
            echo json_encode(['success' => false, 'error' => 'Equipment ID required']);
            exit;
        }

        $equipModel = $this->model('EquipmentModel');
        
        // Get equipment info
        $equipment = $equipModel->getAllEquipment(['q' => '', 'category' => 'all', 'status' => 'all']);
        $equipmentName = 'Équipement';
        foreach ($equipment as $eq) {
            if ($eq['id_equip'] == $equipId) {
                $equipmentName = $eq['nom'];
                break;
            }
        }

        // Get reservations with user info
        $reservations = $equipModel->getEquipmentReservations((int)$equipId);

        echo json_encode([
            'success' => true,
            'equipmentName' => $equipmentName,
            'reservations' => $reservations ?: []
        ]);
        exit;
    }
}
