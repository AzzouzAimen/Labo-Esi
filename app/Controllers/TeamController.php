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
        
        // Get all teams with their members and collect leaders
        $teamsWithMembers = [];
        $teamLeaders = [];
        
        foreach ($teams as $team) {
            // Get members
            $team['members'] = $teamModel->getTeamMembers($team['id_team']);
            $teamsWithMembers[] = $team;
            
            // Collect leader info for organigramme
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
        
        // Get Director Data
        $director = $teamModel->getDirector();
        
        // Prepare data
        $data = [
            'teams' => $teamsWithMembers,
            'teamLeaders' => $teamLeaders,
            'director' => $director
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
