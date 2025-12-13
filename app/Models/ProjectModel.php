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
                u.nom as responsable_nom, u.prenom as responsable_prenom
            FROM projects p
            LEFT JOIN users u ON p.responsable_id = u.id_user
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
    public function filterProjects($domain = null, $status = null) {
        $sql = "
            SELECT 
                p.id_project, p.titre, p.description, p.domaine, p.statut, 
                p.type_financement, p.date_debut, p.image_url,
                u.nom as responsable_nom, u.prenom as responsable_prenom
            FROM projects p
            LEFT JOIN users u ON p.responsable_id = u.id_user
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
        
        $sql .= " ORDER BY p.date_debut DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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
}
