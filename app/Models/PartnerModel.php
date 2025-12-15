<?php
/**
 * PartnerModel
 * Handles database operations for partners
 */
class PartnerModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all partners
     * @return array
     */
    public function getAllPartners() {
        $stmt = $this->db->query("SELECT * FROM partners ORDER BY nom ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get recent partners (just getting random or last added if there was a date, but there isn't, so I'll just take some)
     * Since there is no date_added, I will just take the last ones by ID assuming auto-increment implies order.
     * @param int $limit
     * @return array
     */
    public function getRecentPartners($limit = 2) {
        $stmt = $this->db->prepare("SELECT * FROM partners ORDER BY id_partner DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all partners for display (logo grid)
     * @return array
     */
    public function getAllPartnersForDisplay() {
        $stmt = $this->db->query("SELECT * FROM partners ORDER BY type ASC, nom ASC");
        return $stmt->fetchAll();
    }
}
