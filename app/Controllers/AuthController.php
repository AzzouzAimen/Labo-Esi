<?php
/**
 * AuthController
 * Handles user authentication (login/logout)
 */
class AuthController extends Controller {

    /**
     * Login page
     */
    public function login() {
        $lang = $this->loadLang('fr');
        
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('Dashboard', 'index');
            return;
        }
        
        $data = [
            'error' => null
        ];
        
        // Handle POST request (form submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $data['error'] = 'Veuillez remplir tous les champs';
            } else {
                // Load User Model
                $userModel = $this->model('UserModel');
                
                // Authenticate user
                $user = $userModel->authenticate($username, $password);
                
                if ($user) {
                    // Store user data in session
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['prenom'] = $user['prenom'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['photo'] = $user['photo'];
                    
                    // Redirect to dashboard
                    $this->redirect('Dashboard', 'index');
                    return;
                } else {
                    $data['error'] = $lang['login_error'];
                }
            }
        }
        
        // Load Login View
        $this->view('Login', $data, $lang);
    }

    /**
     * Logout action
     */
    public function logout() {
        // Destroy session
        session_unset();
        session_destroy();
        
        // Redirect to homepage
        $this->redirect('Home', 'index');
    }

    /**
     * Check if user is authenticated (helper method)
     */
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user has a specific role
     */
    public static function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    /**
     * Require authentication (helper method)
     * Redirects to login if not authenticated
     */
    protected function requireAuth() {
        if (!self::isAuthenticated()) {
            $this->redirect('Auth', 'login');
            exit;
        }
    }
}
