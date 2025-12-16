<?php
/**
 * PublicationModel
 * Handles database operations for publications listing & filters
 */
class PublicationModel extends Model {

    private const FILT_YEAR = 'year';
    private const FILT_TYPE = 'type';
    private const FILT_DOMAIN = 'domain';
    private const FILT_AUTHOR = 'author';
    private const FILT_TEAM = 'team';
    private const FILT_PROJECT = 'project';
    private const FILT_Q = 'q';

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
     * Get projects that have at least one publication
     * @return array
     */
    public function getProjects() {
        $stmt = $this->db->query("
            SELECT DISTINCT pr.id_project, pr.titre
            FROM publications p
            JOIN projects pr ON p.project_id = pr.id_project
            ORDER BY pr.titre
        ");
        return $stmt->fetchAll();
    }

    /**
     * Build WHERE clause for search and count
     * @param array $filters
     * @param array &$params
     * @return string
     */
    private function buildWhereClause($filters, &$params) {
        $sql = "";

        if (!empty($filters[self::FILT_YEAR]) && $filters[self::FILT_YEAR] !== 'all') {
            $sql .= " AND YEAR(p.date_publication) = :year";
            $params[':year'] = (int)$filters[self::FILT_YEAR];
        }

        if (!empty($filters[self::FILT_TYPE]) && $filters[self::FILT_TYPE] !== 'all') {
            $sql .= " AND p.type = :type";
            $params[':type'] = $filters[self::FILT_TYPE];
        }

        if (!empty($filters[self::FILT_DOMAIN]) && $filters[self::FILT_DOMAIN] !== 'all') {
            $sql .= " AND pr.domaine = :domain";
            $params[':domain'] = $filters[self::FILT_DOMAIN];
        }

        if (!empty($filters[self::FILT_Q])) {
            $sql .= " AND (p.titre LIKE :q OR p.resume LIKE :q OR p.doi LIKE :q)";
            $params[':q'] = '%' . $filters[self::FILT_Q] . '%';
        }

        if (!empty($filters[self::FILT_AUTHOR]) && $filters[self::FILT_AUTHOR] !== 'all') {
            $sql .= " AND EXISTS (SELECT 1 FROM publication_authors pa2 WHERE pa2.id_pub = p.id_pub AND pa2.id_user = :author)";
            $params[':author'] = (int)$filters[self::FILT_AUTHOR];
        }

        if (!empty($filters[self::FILT_TEAM]) && $filters[self::FILT_TEAM] !== 'all') {
            $sql .= " AND EXISTS (
                SELECT 1
                FROM publication_authors pa3
                JOIN users u3 ON pa3.id_user = u3.id_user
                WHERE pa3.id_pub = p.id_pub AND u3.team_id = :team
            )";
            $params[':team'] = (int)$filters[self::FILT_TEAM];
        }

        if (!empty($filters[self::FILT_PROJECT]) && $filters[self::FILT_PROJECT] !== 'all') {
            $sql .= " AND p.project_id = :project";
            $params[':project'] = (int)$filters[self::FILT_PROJECT];
        }

        return $sql;
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
        $sql .= $this->buildWhereClause($filters, $params);

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
        $sql .= $this->buildWhereClause($filters, $params);

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
