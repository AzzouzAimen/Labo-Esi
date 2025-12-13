<?php
/**
 * TeamController
 * Handles team listing, organizational chart, and member profiles
 */
class TeamController extends Controller {

    /**
     * Teams overview page
     */
    public function index() {
        $lang = $this->loadLang('fr');
        
        // Load Team Model
        $teamModel = $this->model('TeamModel');
        
        // Get all teams with their members
        $teams = $teamModel->getAllTeams();
        
        // For each team, get its members
        $teamsWithMembers = [];
        foreach ($teams as $team) {
            $team['members'] = $teamModel->getTeamMembers($team['id_team']);
            $teamsWithMembers[] = $team;
        }
        
        // Prepare data
        $data = [
            'teams' => $teamsWithMembers
        ];
        
        // Load Team View
        $this->view('Team', $data, $lang);
    }

    /**
     * Organizational chart page
     */
    public function orgChart() {
        $lang = $this->loadLang('fr');
        
        // Load Team Model
        $teamModel = $this->model('TeamModel');
        
        // Get all members
        $members = $teamModel->getAllMembers();
        
        // Group members by role
        $groupedMembers = [
            'admin' => [],
            'enseignant-chercheur' => [],
            'doctorant' => [],
            'etudiant' => []
        ];
        
        foreach ($members as $member) {
            $groupedMembers[$member['role']][] = $member;
        }
        
        // Prepare data
        $data = [
            'groupedMembers' => $groupedMembers
        ];
        
        // Load OrgChart View
        $this->view('OrgChart', $data, $lang);
    }

    /**
     * Member profile page
     */
    public function profile() {
        $lang = $this->loadLang('fr');
        
        // Get user ID from URL
        $userId = $_GET['id'] ?? null;
        
        if (!$userId) {
            $this->redirect('Team', 'index');
            return;
        }
        
        // Load Team Model
        $teamModel = $this->model('TeamModel');
        
        // Get user details
        $user = $teamModel->getUserById($userId);
        
        if (!$user) {
            $this->redirect('Team', 'index');
            return;
        }
        
        // Get user's projects and publications
        $projects = $teamModel->getUserProjects($userId);
        $publications = $teamModel->getUserPublications($userId);
        
        // Prepare data
        $data = [
            'user' => $user,
            'projects' => $projects,
            'publications' => $publications
        ];
        
        // Load MemberProfile View
        $this->view('MemberProfile', $data, $lang);
    }
}
