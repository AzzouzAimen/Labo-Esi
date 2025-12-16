<?php
/**
 * EquipmentModel
 * Handles equipment listing, reservations, and simple usage stats
 */
class EquipmentModel extends Model {

    private const COL_CATEGORY = 'category';
    private const COL_STATUS = 'status';
    private const STATE_MAINTENANCE = 'maintenance';
    private const STATE_RESERVED = 'réservé';
    private const STATE_FREE = 'libre';

    /**
     * Get categories
     * @return array
     */
    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT categorie FROM equipment ORDER BY categorie");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * List equipment with computed current state (maintenance/reserved/free)
     * @param array $filters
     * @return array
     */
    public function getAllEquipment($filters = []) {
        $sql = "
            SELECT
                e.id_equip,
                e.nom,
                e.reference,
                e.categorie,
                e.description,
                e.etat,
                e.image_url,
                e.date_acquisition,
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM maintenances m
                        WHERE m.equip_id = e.id_equip
                        AND m.date_debut <= NOW() AND m.date_fin >= NOW()
                    ) THEN '" . self::STATE_MAINTENANCE . "'
                    WHEN EXISTS (
                        SELECT 1 FROM reservations r
                        WHERE r.equip_id = e.id_equip
                        AND r.date_debut <= NOW() AND r.date_fin >= NOW()
                    ) THEN '" . self::STATE_RESERVED . "'
                    ELSE '" . self::STATE_FREE . "'
                END as etat_actuel
            FROM equipment e
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters[self::COL_CATEGORY]) && $filters[self::COL_CATEGORY] !== 'all') {
            $sql .= " AND e.categorie = :cat";
            $params[':cat'] = $filters[self::COL_CATEGORY];
        }

        if (!empty($filters['q'])) {
            $sql .= " AND (e.nom LIKE :q OR e.reference LIKE :q OR e.description LIKE :q)";
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        // Filter by computed state
        if (!empty($filters[self::COL_STATUS]) && $filters[self::COL_STATUS] !== 'all') {
            $sql .= " AND (
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM maintenances m
                        WHERE m.equip_id = e.id_equip
                        AND m.date_debut <= NOW() AND m.date_fin >= NOW()
                    ) THEN '" . self::STATE_MAINTENANCE . "'
                    WHEN EXISTS (
                        SELECT 1 FROM reservations r
                        WHERE r.equip_id = e.id_equip
                        AND r.date_debut <= NOW() AND r.date_fin >= NOW()
                    ) THEN '" . self::STATE_RESERVED . "'
                    ELSE '" . self::STATE_FREE . "'
                END
            ) = :status";
            $params[':status'] = $filters[self::COL_STATUS];
        }

        $sql .= " ORDER BY e.nom";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get reservations for a user
     * @param int $userId
     * @return array
     */
    public function getUserReservations($userId) {
        $stmt = $this->db->prepare("
            SELECT
                r.id_res,
                r.user_id,
                r.equip_id,
                r.date_debut,
                r.date_fin,
                r.motif,
                e.nom as equip_nom,
                e.reference as equip_reference,
                e.categorie as equip_categorie,
                CASE
                    WHEN r.date_fin < NOW() THEN 'passée'
                    WHEN r.date_debut > NOW() THEN 'à venir'
                    ELSE 'en cours'
                END as statut_reservation
            FROM reservations r
            JOIN equipment e ON r.equip_id = e.id_equip
            WHERE r.user_id = :uid
            ORDER BY r.date_debut DESC
        ");
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Create a reservation with basic overlap checks
     * @return array [success=>bool, error=>string|null]
     */
    public function createReservation($userId, $equipId, $start, $end, $motif = '') {
        try {
            $startDt = new DateTime($start);
            $endDt = new DateTime($end);

            if ($endDt <= $startDt) {
                return [KEY_SUCCESS => false, KEY_ERROR => 'La date de fin doit être après la date de début.'];
            }

            // Check overlaps with existing reservations
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as c
                FROM reservations
                WHERE equip_id = :eid
                AND NOT (date_fin <= :start OR date_debut >= :end)
            ");
            $stmt->execute([
                ':eid' => (int)$equipId,
                ':start' => $startDt->format('Y-m-d H:i:s'),
                ':end' => $endDt->format('Y-m-d H:i:s')
            ]);
            $row = $stmt->fetch();
            if ((int)($row['c'] ?? 0) > 0) {
                return [KEY_SUCCESS => false, KEY_ERROR => 'Ce créneau est déjà réservé pour cet équipement.'];
            }

            // Check overlaps with maintenances
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as c
                FROM maintenances
                WHERE equip_id = :eid
                AND NOT (date_fin <= :start OR date_debut >= :end)
            ");
            $stmt->execute([
                ':eid' => (int)$equipId,
                ':start' => $startDt->format('Y-m-d H:i:s'),
                ':end' => $endDt->format('Y-m-d H:i:s')
            ]);
            $row = $stmt->fetch();
            if ((int)($row['c'] ?? 0) > 0) {
                return [KEY_SUCCESS => false, KEY_ERROR => 'Cet équipement est en maintenance pendant ce créneau.'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO reservations (user_id, equip_id, date_debut, date_fin, motif)
                VALUES (:uid, :eid, :start, :end, :motif)
            ");
            $ok = $stmt->execute([
                ':uid' => (int)$userId,
                ':eid' => (int)$equipId,
                ':start' => $startDt->format('Y-m-d H:i:s'),
                ':end' => $endDt->format('Y-m-d H:i:s'),
                ':motif' => $motif
            ]);

            return [KEY_SUCCESS => (bool)$ok, KEY_ERROR => $ok ? null : 'Erreur lors de la réservation.'];
        } catch (Exception $e) {
            return [KEY_SUCCESS => false, KEY_ERROR => 'Erreur: ' . $e->getMessage()];
        }
    }

    /**
     * Simple usage stats: most reserved equipment
     * @param int $limit
     * @return array
     */
    public function getUsageStats($limit = 5) {
        $limit = max(1, min(10, (int)$limit));
        $stmt = $this->db->prepare("
            SELECT
                e.id_equip,
                e.nom,
                e.categorie,
                COUNT(r.id_res) as nb_reservations
            FROM equipment e
            LEFT JOIN reservations r ON r.equip_id = e.id_equip
            GROUP BY e.id_equip
            ORDER BY nb_reservations DESC, e.nom ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
