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
        
        // Prepare data
        $data = [
            'projects' => $projects,
            'domains' => $domains,
            'statuses' => $statuses
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
        
        // Filter projects
        $projects = $projectModel->filterProjects($domain, $status);
        
        // Return JSON response
        $this->json([
            'success' => true,
            'data' => $projects
        ]);
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
