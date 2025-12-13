<?php
/**
 * TeamModel
 * Handles database operations for teams and members
 */
class TeamModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all teams with their leaders
     * @return array
     */
    public function getAllTeams() {
        $stmt = $this->db->query("
            SELECT 
                t.id_team, t.nom, t.description,
                u.id_user as chef_id, u.nom as chef_nom, u.prenom as chef_prenom,
                u.photo as chef_photo, u.grade as chef_grade, u.email as chef_email
            FROM teams t
            LEFT JOIN users u ON t.chef_id = u.id_user
            ORDER BY t.nom
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get a single team by ID
     * @param int $id
     * @return array|false
     */
    public function getTeamById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                t.id_team, t.nom, t.description,
                u.id_user as chef_id, u.nom as chef_nom, u.prenom as chef_prenom,
                u.photo as chef_photo, u.grade as chef_grade, u.email as chef_email,
                u.domaine_recherche as chef_domaine
            FROM teams t
            LEFT JOIN users u ON t.chef_id = u.id_user
            WHERE t.id_team = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get team members (excluding the leader)
     * @param int $teamId
     * @return array
     */
    public function getTeamMembers($teamId) {
        $stmt = $this->db->prepare("
            SELECT 
                u.id_user, u.nom, u.prenom, u.photo, u.grade, u.email,
                u.domaine_recherche, tm.role_dans_equipe
            FROM team_members tm
            JOIN users u ON tm.usr_id = u.id_user
            JOIN teams t ON tm.team_id = t.id_team
            WHERE tm.team_id = :team_id 
            AND u.id_user != t.chef_id
            ORDER BY u.nom, u.prenom
        ");
        $stmt->execute([':team_id' => $teamId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all laboratory members (for organizational chart)
     * @return array
     */
    public function getAllMembers() {
        $stmt = $this->db->query("
            SELECT 
                u.id_user, u.nom, u.prenom, u.photo, u.grade, 
                u.poste, u.domaine_recherche, u.email, u.role
            FROM users u
            WHERE u.role IN ('admin', 'enseignant-chercheur', 'doctorant', 'etudiant')
            ORDER BY 
                CASE u.role
                    WHEN 'admin' THEN 1
                    WHEN 'enseignant-chercheur' THEN 2
                    WHEN 'doctorant' THEN 3
                    WHEN 'etudiant' THEN 4
                END,
                u.nom, u.prenom
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get user by ID
     * @param int $userId
     * @return array|false
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                id_user, username, nom, prenom, email, photo, grade, 
                poste, domaine_recherche, role
            FROM users
            WHERE id_user = :id
        ");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Get user's projects
     * @param int $userId
     * @return array
     */
    public function getUserProjects($userId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT
                p.id_project, p.titre, p.domaine, p.statut, p.image_url, p.date_debut,
                p.responsable_id
            FROM projects p
            LEFT JOIN project_members pm ON p.id_project = pm.id_project
            WHERE p.responsable_id = :user_id1 
            OR pm.id_user = :user_id2
            ORDER BY p.date_debut DESC
        ");
        $stmt->execute([':user_id1' => $userId, ':user_id2' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get user's publications
     * @param int $userId
     * @return array
     */
    public function getUserPublications($userId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT
                p.id_pub, p.titre, p.date_publication, p.type, p.lien_pdf
            FROM publications p
            JOIN publication_authors pa ON p.id_pub = pa.id_pub
            WHERE pa.id_user = :user_id
            ORDER BY p.date_publication DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
