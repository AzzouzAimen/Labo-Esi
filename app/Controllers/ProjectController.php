<?php
/**
 * ProjectController
 * Handles project listing, filtering, and details
 */
class ProjectController extends Controller {

    /**
     * Project catalog page
     */
    public function index() {
        $lang = $this->loadLang('fr');
        
        // Load Project Model
        $projectModel = $this->model('ProjectModel');
        
        // Get all projects
        $projects = $projectModel->getAllProjects();
        $domains = $projectModel->getDomains();
        $statuses = $projectModel->getStatuses();
        $supervisors = $projectModel->getSupervisors();
        
        // Prepare data
        $data = [
            'projects' => $projects,
            'domains' => $domains,
            'statuses' => $statuses,
            'supervisors' => $supervisors
        ];
        
        // Load Project View
        $this->view('Project', $data, $lang);
    }

    /**
     * AJAX filter action
     * Returns JSON response
     */
    public function filter() {
        // Load Project Model
        $projectModel = $this->model('ProjectModel');
        
        // Get filter parameters
        $domain = $_GET['domain'] ?? null;
        $status = $_GET['status'] ?? null;
        $supervisor = $_GET['supervisor'] ?? null;
        
        // Filter projects
        $projects = $projectModel->filterProjects($domain, $status, $supervisor);
        
        // Return JSON response
        $this->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    /**
     * Project edit page (only for project manager)
     */
    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('Auth', 'login');
            return;
        }

        $lang = $this->loadLang('fr');
        $projectId = $_GET['id'] ?? null;

        if (!$projectId) {
            $this->redirect('Dashboard', 'index');
            return;
        }

        $projectModel = $this->model('ProjectModel');
        $project = $projectModel->getProjectById((int)$projectId);

        if (!$project || (int)$project['responsable_id'] !== (int)$_SESSION['user_id']) {
            $this->redirect('Dashboard', 'index');
            return;
        }

        $data = [
            'project' => $project,
            'domains' => $projectModel->getDomains(),
            'statuses' => $projectModel->getStatuses(),
            'success' => null,
            'error' => null
        ];

        $this->view('ProjectEdit', $data, $lang);
    }

    /**
     * Project update (POST) - only for project manager
     */
    public function update() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('Auth', 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('Dashboard', 'index');
            return;
        }

        $projectId = $_POST['id_project'] ?? null;
        if (!$projectId) {
            $this->redirect('Dashboard', 'index');
            return;
        }

        $projectModel = $this->model('ProjectModel');

        $updateData = [
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'domaine' => $_POST['domaine'] ?? '',
            'statut' => $_POST['statut'] ?? '',
            'type_financement' => $_POST['type_financement'] ?? '',
            'image_url' => $_POST['image_url'] ?? ''
        ];

        $ok = $projectModel->updateProjectByManager((int)$projectId, (int)$_SESSION['user_id'], $updateData);

        $redirectParams = ['id' => (int)$projectId];
        if ($ok) {
            $redirectParams['success'] = 1;
        } else {
            $redirectParams['error'] = 1;
        }

        $this->redirect('Project', 'edit', $redirectParams);
    }

    /**
     * Project detail page
     */
    public function detail() {
        $lang = $this->loadLang('fr');
        
        // Get project ID from URL
        $projectId = $_GET['id'] ?? null;
        
        if (!$projectId) {
            $this->redirect('Project', 'index');
            return;
        }
        
        // Load Project Model
        $projectModel = $this->model('ProjectModel');
        
        // Get project details
        $project = $projectModel->getProjectById($projectId);
        
        if (!$project) {
            $this->redirect('Project', 'index');
            return;
        }
        
        // Get related data
        $members = $projectModel->getProjectMembers($projectId);
        $partners = $projectModel->getProjectPartners($projectId);
        $publications = $projectModel->getProjectPublications($projectId);
        
        // Prepare data
        $data = [
            'project' => $project,
            'members' => $members,
            'partners' => $partners,
            'publications' => $publications
        ];
        
        // Load Project Detail View
        $this->view('ProjectDetail', $data, $lang);
    }
}
