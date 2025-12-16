<?php
/**
 * DashboardController
 * User dashboard after authentication
 */
class DashboardController extends Controller {

    /**
     * Dashboard home
     */
    public function index() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('Auth', 'login');
            return;
        }
        
        $lang = $this->loadLang('fr');
        
        // Load models
        $teamModel = $this->model('TeamModel');
        $userModel = $this->model('UserModel');
        $equipmentModel = $this->model('EquipmentModel');
        
        // Get user data
        $user = $userModel->getUserById($_SESSION['user_id']);
        $projects = $teamModel->getUserProjects($_SESSION['user_id']);
        $publications = $teamModel->getUserPublications($_SESSION['user_id']);
        $reservations = $equipmentModel->getUserReservations($_SESSION['user_id']);
        
        // Prepare data
        $data = [
            'user' => $user,
            'projects' => $projects,
            'publications' => $publications,
            'reservations' => $reservations
        ];
        
        // Load Dashboard View
        $this->view('Dashboard', $data, $lang);
    }

    /**
     * Profile update page
     */
    public function profile() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('Auth', 'login');
            return;
        }
        
        $lang = $this->loadLang('fr');
        $userModel = $this->model('UserModel');
        
        $data = [
            KEY_SUCCESS => null,
            KEY_ERROR => null
        ];
        
        // Handle POST request (profile update)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updateData = [
                'domaine_recherche' => $_POST['domaine_recherche'] ?? '',
                'poste' => $_POST['poste'] ?? ''
            ];
            
            // Handle file upload (photo)
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = UPLOAD_PATH . 'profiles' . DIRECTORY_SEPARATOR;
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = time() . '_' . basename($_FILES['photo']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    $updateData['photo'] = 'uploads/profiles/' . $fileName;
                    $_SESSION['photo'] = $updateData['photo'];
                }
            }
            
            if ($userModel->updateProfile($_SESSION['user_id'], $updateData)) {
                $data[KEY_SUCCESS] = 'Profil mis à jour avec succès';
            } else {
                $data[KEY_ERROR] = 'Erreur lors de la mise à jour du profil';
            }
        }
        
        // Get current user data
        $data['user'] = $userModel->getUserById($_SESSION['user_id']);
        
        // Load Profile Edit View
        $this->view('ProfileEdit', $data, $lang);
    }
}
