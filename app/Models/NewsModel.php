<?php
/**
 * NewsModel
 * Handles database operations for events/news
 */
class NewsModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get recent news/events for slideshow (limit 5)
     * @return array
     */
    public function getRecentNews($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT id_event, titre, description, date_event, type, image_url, lieu
            FROM events
            ORDER BY date_event DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming events
     * @return array
     */
    public function getUpcomingEvents($limit = 3) {
        $stmt = $this->db->prepare("
            SELECT id_event, titre, description, date_event, type, image_url, lieu
            FROM events
            WHERE date_event >= NOW()
            ORDER BY date_event ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming events with pagination
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getUpcomingEventsPaginated($page = 1, $perPage = 3) {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare("
            SELECT id_event, titre, description, date_event, type, image_url, lieu
            FROM events
            WHERE date_event >= NOW()
            ORDER BY date_event ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count upcoming events
     * @return int
     */
    public function countUpcomingEvents() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM events WHERE date_event >= NOW()");
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get a single event by ID
     * @param int $eventId
     * @return array|false
     */
    public function getEventById($eventId) {
        $stmt = $this->db->prepare("
            SELECT id_event, titre, description, date_event, type, image_url, lieu
            FROM events
            WHERE id_event = :id
        ");
        $stmt->execute([':id' => (int)$eventId]);
        return $stmt->fetch();
    }

    /**
     * Get all events with pagination
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getAllEvents($page = 1, $perPage = 9) {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->db->prepare("
            SELECT id_event, titre, description, date_event, type, image_url, lieu
            FROM events
            ORDER BY date_event DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count total events
     * @return int
     */
    public function countEvents() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM events");
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
}
