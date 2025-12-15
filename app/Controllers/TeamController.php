<?php
/**
 * TeamController
 * Handles team listing, organizational chart, and member profiles
 */
class TeamController extends Controller {

    /**
     * Presentation page - Shows introduction, organigramme, and team structure
     */
    public function presentation() {
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
        
        // Get team leaders for organigramme
        $teamLeaders = [];
        foreach ($teams as $team) {
            if ($team['chef_id']) {
                $teamLeaders[] = [
                    'id' => $team['chef_id'],
                    'nom' => $team['chef_nom'],
                    'prenom' => $team['chef_prenom'],
                    'grade' => $team['chef_grade'],
                    'photo' => $team['chef_photo'],
                    'team_name' => $team['nom']
                ];
            }
        }
        
        // Prepare data
        $data = [
            'teams' => $teamsWithMembers,
            'teamLeaders' => $teamLeaders
        ];
        
        // Load Presentation View
        $this->view('Team', $data, $lang);
    }

    /**
     * Members directory page - Searchable/filterable member list
     */
    public function membres() {
        $lang = $this->loadLang('fr');
        
        // Load Team Model
        $teamModel = $this->model('TeamModel');
        
        // Get all members
        $members = $teamModel->getAllMembers();
        
        // Get all teams for filter
        $teams = $teamModel->getAllTeams();
        
        // Extract unique grades
        $grades = [];
        foreach ($members as $member) {
            if (!empty($member['grade'])) {
                $grades[$member['grade']] = true;
            }
        }
        $grades = array_keys($grades);
        sort($grades);
        
        // Prepare data
        $data = [
            'members' => $members,
            'teams' => $teams,
            'grades' => $grades
        ];
        
        // Load Members Directory View
        $this->view('MembersDirectory', $data, $lang);
    }

    /**
     * Teams overview page (kept for backward compatibility)
     */
    public function index() {
        // Redirect to presentation page
        $this->redirect('Team', 'presentation');
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
