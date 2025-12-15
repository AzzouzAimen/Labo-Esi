<?php
/**
 * PublicationModel
 * Handles database operations for publications listing & filters
 */
class PublicationModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get available years from publications
     * @return array
     */
    public function getYears() {
        $stmt = $this->db->query("
            SELECT DISTINCT YEAR(date_publication) as annee
            FROM publications
            WHERE date_publication IS NOT NULL
            ORDER BY annee DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get publication types
     * @return array
     */
    public function getTypes() {
        $stmt = $this->db->query("
            SELECT DISTINCT type
            FROM publications
            ORDER BY type
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get domains from linked projects
     * @return array
     */
    public function getDomains() {
        $stmt = $this->db->query("
            SELECT DISTINCT pr.domaine
            FROM publications p
            JOIN projects pr ON p.project_id = pr.id_project
            ORDER BY pr.domaine
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get authors that have at least one publication
     * @return array
     */
    public function getAuthors() {
        $stmt = $this->db->query("
            SELECT DISTINCT u.id_user, u.prenom, u.nom
            FROM publication_authors pa
            JOIN users u ON pa.id_user = u.id_user
            ORDER BY u.nom, u.prenom
        ");
        return $stmt->fetchAll();
    }

    /**
     * Search publications with filters
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @param string $sort
     * @return array
     */
    public function searchPublications($filters = [], $page = 1, $perPage = 6, $sort = 'date_desc') {
        $page = max(1, (int)$page);
        $perPage = max(1, min(24, (int)$perPage));
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT
                p.id_pub,
                p.titre,
                p.resume,
                p.date_publication,
                p.lien_pdf,
                p.doi,
                p.type,
                pr.domaine as domaine,
                GROUP_CONCAT(DISTINCT CONCAT(u.prenom, ' ', u.nom) ORDER BY u.nom, u.prenom SEPARATOR ', ') as auteurs
            FROM publications p
            LEFT JOIN projects pr ON p.project_id = pr.id_project
            LEFT JOIN publication_authors pa ON p.id_pub = pa.id_pub
            LEFT JOIN users u ON pa.id_user = u.id_user
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['year']) && $filters['year'] !== 'all') {
            $sql .= " AND YEAR(p.date_publication) = :year";
            $params[':year'] = (int)$filters['year'];
        }

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $sql .= " AND p.type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['domain']) && $filters['domain'] !== 'all') {
            $sql .= " AND pr.domaine = :domain";
            $params[':domain'] = $filters['domain'];
        }

        if (!empty($filters['q'])) {
            $sql .= " AND (p.titre LIKE :q OR p.resume LIKE :q OR p.doi LIKE :q)";
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['author']) && $filters['author'] !== 'all') {
            $sql .= " AND EXISTS (SELECT 1 FROM publication_authors pa2 WHERE pa2.id_pub = p.id_pub AND pa2.id_user = :author)";
            $params[':author'] = (int)$filters['author'];
        }

        if (!empty($filters['team']) && $filters['team'] !== 'all') {
            $sql .= " AND EXISTS (
                SELECT 1
                FROM publication_authors pa3
                JOIN users u3 ON pa3.id_user = u3.id_user
                WHERE pa3.id_pub = p.id_pub AND u3.team_id = :team
            )";
            $params[':team'] = (int)$filters['team'];
        }

        $sql .= " GROUP BY p.id_pub";

        $orderBy = "p.date_publication DESC";
        switch ($sort) {
            case 'date_asc':
                $orderBy = "p.date_publication ASC";
                break;
            case 'title_asc':
                $orderBy = "p.titre ASC";
                break;
            case 'title_desc':
                $orderBy = "p.titre DESC";
                break;
            case 'date_desc':
            default:
                $orderBy = "p.date_publication DESC";
                break;
        }

        $sql .= " ORDER BY $orderBy LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Count publications with same filters
     * @param array $filters
     * @return int
     */
    public function countPublications($filters = []) {
        $sql = "
            SELECT COUNT(DISTINCT p.id_pub) as total
            FROM publications p
            LEFT JOIN projects pr ON p.project_id = pr.id_project
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['year']) && $filters['year'] !== 'all') {
            $sql .= " AND YEAR(p.date_publication) = :year";
            $params[':year'] = (int)$filters['year'];
        }

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $sql .= " AND p.type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['domain']) && $filters['domain'] !== 'all') {
            $sql .= " AND pr.domaine = :domain";
            $params[':domain'] = $filters['domain'];
        }

        if (!empty($filters['q'])) {
            $sql .= " AND (p.titre LIKE :q OR p.resume LIKE :q OR p.doi LIKE :q)";
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['author']) && $filters['author'] !== 'all') {
            $sql .= " AND EXISTS (SELECT 1 FROM publication_authors pa2 WHERE pa2.id_pub = p.id_pub AND pa2.id_user = :author)";
            $params[':author'] = (int)$filters['author'];
        }

        if (!empty($filters['team']) && $filters['team'] !== 'all') {
            $sql .= " AND EXISTS (
                SELECT 1
                FROM publication_authors pa3
                JOIN users u3 ON pa3.id_user = u3.id_user
                WHERE pa3.id_pub = p.id_pub AND u3.team_id = :team
            )";
            $params[':team'] = (int)$filters['team'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Get recent publications
     * @param int $limit
     * @return array
     */
    public function getRecentPublications($limit = 3) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id_pub, p.titre, YEAR(p.date_publication) as annee
            FROM publications p
            ORDER BY p.date_publication DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
