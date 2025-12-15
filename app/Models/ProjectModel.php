<?php
/**
 * ProjectModel
 * Handles database operations for research projects
 */
class ProjectModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all projects with manager information
     * @return array
     */
    public function getAllProjects() {
        $stmt = $this->db->query("
            SELECT 
                p.id_project, p.titre, p.description, p.domaine, p.statut, 
                p.type_financement, p.date_debut, p.image_url,
                u.nom as responsable_nom, u.prenom as responsable_prenom,
                COALESCE(pm_stats.membres_count, 0) as membres_count,
                pm_stats.membres_noms as membres_noms
            FROM projects p
            LEFT JOIN users u ON p.responsable_id = u.id_user
            LEFT JOIN (
                SELECT 
                    pm.id_project,
                    COUNT(pm.id_user) as membres_count,
                    GROUP_CONCAT(CONCAT(u2.prenom, ' ', u2.nom) SEPARATOR ', ') as membres_noms
                FROM project_members pm
                JOIN users u2 ON pm.id_user = u2.id_user
                GROUP BY pm.id_project
            ) pm_stats ON pm_stats.id_project = p.id_project
            ORDER BY p.date_debut DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Filter projects by domain and/or status
     * @param string|null $domain
     * @param string|null $status
     * @return array
     */
    public function filterProjects($domain = null, $status = null, $supervisorId = null) {
        $sql = "
            SELECT 
                p.id_project, p.titre, p.description, p.domaine, p.statut, 
                p.type_financement, p.date_debut, p.image_url,
                u.nom as responsable_nom, u.prenom as responsable_prenom,
                COALESCE(pm_stats.membres_count, 0) as membres_count,
                pm_stats.membres_noms as membres_noms
            FROM projects p
            LEFT JOIN users u ON p.responsable_id = u.id_user
            LEFT JOIN (
                SELECT 
                    pm.id_project,
                    COUNT(pm.id_user) as membres_count,
                    GROUP_CONCAT(CONCAT(u2.prenom, ' ', u2.nom) SEPARATOR ', ') as membres_noms
                FROM project_members pm
                JOIN users u2 ON pm.id_user = u2.id_user
                GROUP BY pm.id_project
            ) pm_stats ON pm_stats.id_project = p.id_project
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($domain && $domain !== 'all') {
            $sql .= " AND p.domaine = :domain";
            $params[':domain'] = $domain;
        }
        
        if ($status && $status !== 'all') {
            $sql .= " AND p.statut = :status";
            $params[':status'] = $status;
        }

        if ($supervisorId && $supervisorId !== 'all') {
            $sql .= " AND p.responsable_id = :supervisor";
            $params[':supervisor'] = (int)$supervisorId;
        }
        
        $sql .= " ORDER BY p.date_debut DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get supervisors (responsables) that are linked to at least one project
     * @return array
     */
    public function getSupervisors() {
        $stmt = $this->db->query("
            SELECT DISTINCT u.id_user, u.prenom, u.nom
            FROM projects p
            JOIN users u ON p.responsable_id = u.id_user
            ORDER BY u.nom, u.prenom
        ");
        return $stmt->fetchAll();
    }

    /**
     * Update project (used by manager from dashboard)
     * @param int $projectId
     * @param int $managerId
     * @param array $data
     * @return bool
     */
    public function updateProjectByManager($projectId, $managerId, $data) {
        $allowed = [
            'titre',
            'description',
            'domaine',
            'statut',
            'type_financement',
            'image_url'
        ];

        $fields = [];
        $params = [':id' => (int)$projectId, ':manager' => (int)$managerId];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $fields[] = "$key = :$key";
                $params[":" . $key] = $data[$key];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE id_project = :id AND responsable_id = :manager";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get a single project by ID with full details
     * @param int $id
     * @return array|false
     */
    public function getProjectById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id_project, p.titre, p.description, p.domaine, p.statut, 
                p.type_financement, p.date_debut, p.image_url,
                u.id_user as responsable_id, u.nom as responsable_nom, 
                u.prenom as responsable_prenom, u.email as responsable_email
            FROM projects p
            LEFT JOIN users u ON p.responsable_id = u.id_user
            WHERE p.id_project = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get project members
     * @param int $projectId
     * @return array
     */
    public function getProjectMembers($projectId) {
        $stmt = $this->db->prepare("
            SELECT 
                u.id_user, u.nom, u.prenom, u.photo, u.grade,
                pm.role_dans_projet
            FROM project_members pm
            JOIN users u ON pm.id_user = u.id_user
            WHERE pm.id_project = :project_id
        ");
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    /**
     * Get project partners
     * @param int $projectId
     * @return array
     */
    public function getProjectPartners($projectId) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id_partner, p.nom, p.logo_url, p.type, p.site_web
            FROM project_partners pp
            JOIN partners p ON pp.id_partner = p.id_partner
            WHERE pp.id_project = :project_id
        ");
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    /**
     * Get project publications
     * @param int $projectId
     * @return array
     */
    public function getProjectPublications($projectId) {
        $stmt = $this->db->prepare("
            SELECT 
                id_pub, titre, resume, date_publication, lien_pdf, doi, type
            FROM publications
            WHERE project_id = :project_id
            ORDER BY date_publication DESC
        ");
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    /**
     * Get available domains
     * @return array
     */
    public function getDomains() {
        $stmt = $this->db->query("
            SELECT DISTINCT domaine 
            FROM projects 
            ORDER BY domaine
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get available statuses
     * @return array
     */
    public function getStatuses() {
        $stmt = $this->db->query("
            SELECT DISTINCT statut 
            FROM projects 
            ORDER BY statut
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get recent projects
     * @param int $limit
     * @return array
     */
    public function getRecentProjects($limit = 2) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id_project, p.titre, p.domaine, p.date_debut
            FROM projects p
            ORDER BY p.date_debut DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
