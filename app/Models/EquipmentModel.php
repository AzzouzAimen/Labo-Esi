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
     * @param bool $includeStatus Include the new status field
     * @return array
     */
    public function getUserReservations($userId, $includeStatus = true) {
        $statusSelect = $includeStatus ? ", r.status, r.is_urgent, r.urgent_reason" : "";
        
        $stmt = $this->db->prepare("
            SELECT
                r.id_res,
                r.user_id,
                r.equip_id,
                r.date_debut,
                r.date_fin,
                r.motif
                {$statusSelect},
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
     * Create a reservation with conflict detection (NEW LOGIC)
     * - Allows overlapping reservations
     * - Marks conflicts automatically
     * - Supports urgent reservations
     * 
     * @param int $userId User making the reservation
     * @param int $equipId Equipment to reserve
     * @param string $start Start datetime
     * @param string $end End datetime
     * @param string $motif Reason for reservation
     * @param bool $isUrgent Whether this is an urgent reservation
     * @param string $urgentReason Explanation for urgency
     * @return array [success=>bool, error=>string|null, reservation_id=>int|null]
     */
    public function createReservation($userId, $equipId, $start, $end, $motif = '', $isUrgent = false, $urgentReason = '') {
        try {
            $startDt = new DateTime($start);
            $endDt = new DateTime($end);

            if ($endDt <= $startDt) {
                return [KEY_SUCCESS => false, KEY_ERROR => 'La date de fin doit être après la date de début.', 'reservation_id' => null];
            }

            // Check overlaps with maintenances (still block maintenance periods)
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
                return [KEY_SUCCESS => false, KEY_ERROR => 'Cet équipement est en maintenance pendant ce créneau.', 'reservation_id' => null];
            }

            // CRITICAL: No longer blocking overlaps - just detect them
            // Insert the reservation with initial status
            $stmt = $this->db->prepare("
                INSERT INTO reservations (user_id, equip_id, date_debut, date_fin, motif, is_urgent, urgent_reason, status)
                VALUES (:uid, :eid, :start, :end, :motif, :urgent, :urgent_reason, 'confirmed')
            ");
            $ok = $stmt->execute([
                ':uid' => (int)$userId,
                ':eid' => (int)$equipId,
                ':start' => $startDt->format('Y-m-d H:i:s'),
                ':end' => $endDt->format('Y-m-d H:i:s'),
                ':motif' => $motif,
                ':urgent' => $isUrgent ? 1 : 0,
                ':urgent_reason' => $isUrgent ? $urgentReason : null
            ]);

            if (!$ok) {
                return [KEY_SUCCESS => false, KEY_ERROR => 'Erreur lors de la réservation.', 'reservation_id' => null];
            }

            $newReservationId = (int)$this->db->lastInsertId();

            // Now check for overlaps and mark conflicts
            $this->detectAndMarkConflicts($equipId, $newReservationId, $startDt->format('Y-m-d H:i:s'), $endDt->format('Y-m-d H:i:s'));

            return [KEY_SUCCESS => true, KEY_ERROR => null, 'reservation_id' => $newReservationId];
        } catch (Exception $e) {
            return [KEY_SUCCESS => false, KEY_ERROR => 'Erreur: ' . $e->getMessage(), 'reservation_id' => null];
        }
    }

    /**
     * Detect and mark conflicting reservations
     * 
     * @param int $equipId Equipment ID
     * @param int $newReservationId The newly created reservation ID
     * @param string $start Start datetime
     * @param string $end End datetime
     */
    private function detectAndMarkConflicts($equipId, $newReservationId, $start, $end) {
        // Find all overlapping reservations (excluding the new one)
        $stmt = $this->db->prepare("
            SELECT id_res
            FROM reservations
            WHERE equip_id = :eid
            AND id_res != :new_id
            AND NOT (date_fin <= :start OR date_debut >= :end)
        ");
        $stmt->execute([
            ':eid' => (int)$equipId,
            ':new_id' => $newReservationId,
            ':start' => $start,
            ':end' => $end
        ]);
        $overlapping = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // If there are overlaps, mark all involved reservations as 'conflict'
        if (count($overlapping) > 0) {
            // Mark the new reservation as conflict
            $updateStmt = $this->db->prepare("UPDATE reservations SET status = 'conflict' WHERE id_res = :id");
            $updateStmt->execute([':id' => $newReservationId]);

            // Mark all overlapping reservations as conflict
            foreach ($overlapping as $overlapId) {
                $updateStmt->execute([':id' => (int)$overlapId]);
            }
        }
    }

    /**
     * Get all reservations for a specific equipment (for history/conflict view)
     * 
     * @param int $equipId Equipment ID
     * @param string $startDate Optional start date filter
     * @param string $endDate Optional end date filter
     * @return array
     */
    public function getEquipmentReservations($equipId, $startDate = null, $endDate = null) {
        $sql = "
            SELECT
                r.id_res,
                r.user_id,
                r.date_debut,
                r.date_fin,
                r.motif,
                r.status,
                r.is_urgent,
                r.urgent_reason,
                u.prenom,
                u.nom,
                u.email
            FROM reservations r
            JOIN users u ON r.user_id = u.id_user
            WHERE r.equip_id = :eid
        ";
        
        $params = [':eid' => (int)$equipId];
        
        if ($startDate) {
            $sql .= " AND r.date_fin >= :start";
            $params[':start'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND r.date_debut <= :end";
            $params[':end'] = $endDate;
        }
        
        $sql .= " ORDER BY r.date_debut ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        // Add combined user_name field for easier display
        foreach ($results as &$row) {
            $row['user_name'] = trim(($row['prenom'] ?? '') . ' ' . ($row['nom'] ?? ''));
            if (empty($row['user_name'])) {
                $row['user_name'] = $row['email'] ?? 'Utilisateur inconnu';
            }
        }
        
        return $results;
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
