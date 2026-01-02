<?php
/**
 * UpcomingEvents Component
 * 
 * Displays a list of upcoming events with pagination
 */
class UpcomingEvents extends Component {
    /**
     * Render the upcoming events HTML
     * 
     * Expected props:
     * - upcomingEvents: Array of upcoming events (paginated)
     * - allUpcomingEvents: Array of all upcoming events (for JS pagination)
     * - eventsPage: Current page number
     * - eventsTotalPages: Total number of pages
     * - eventsPerPage: Events per page
     * - lang: Array of language strings
     * - baseUrl: String base URL
     * 
     * @return string The rendered HTML
     */
    public function render(): string {
        $upcomingEvents = $this->props['upcomingEvents'] ?? [];
        $allUpcomingEvents = $this->props['allUpcomingEvents'] ?? [];
        $eventsPage = $this->props['eventsPage'] ?? 1;
        $eventsTotalPages = $this->props['eventsTotalPages'] ?? 1;
        $eventsPerPage = $this->props['eventsPerPage'] ?? 3;
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? BASE_URL;
        
        if (empty($upcomingEvents)) {
            return '';
        }
        
        ob_start();
        ?>
        <!-- Upcoming Events Section -->
        <section class="upcoming-events mt-4">
            <h2 class="section-title"><?= $lang['upcoming_events'] ?? 'Événements à Venir' ?></h2>
            <div class="card-grid" id="upcoming-events-grid"
                 data-page="<?= (int)$eventsPage ?>"
                 data-total-pages="<?= (int)$eventsTotalPages ?>"
                 data-per-page="<?= (int)$eventsPerPage ?>"
                 data-all-events="<?= htmlspecialchars(json_encode($allUpcomingEvents), ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach ($upcomingEvents as $event): ?>
                <div class="card">
                    <?php if ($event['image_url']): ?>
                        <img src="<?= $baseUrl . htmlspecialchars($event['image_url'], ENT_QUOTES, 'UTF-8') ?>" 
                             alt="<?= htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8') ?>"
                             onerror="this.src='<?= $baseUrl ?>assets/img/event-placeholder.jpg'">
                    <?php else: ?>
                        <img src="<?= $baseUrl ?>assets/img/event-placeholder.jpg" alt="<?= htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($event['titre'], ENT_QUOTES, 'UTF-8') ?></h3>
                        
                        <div class="card-meta">
                            <span><strong><?= $lang['event_date'] ?? 'Date' ?>:</strong> 
                                <?= date('d/m/Y H:i', strtotime($event['date_event'])) ?>
                            </span>
                            <?php if ($event['lieu']): ?>
                            <span><strong><?= $lang['event_location'] ?? 'Lieu' ?>:</strong> 
                                <?= htmlspecialchars($event['lieu'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-2">
                            <span class="badge badge-primary"><?= htmlspecialchars($event['type'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        
                        <p class="card-text">
                            <?= htmlspecialchars(substr($event['description'], 0, 120), ENT_QUOTES, 'UTF-8') ?>...
                        </p>
                        
                        <a href="<?= $baseUrl ?>index.php?controller=Event&action=detail&id=<?= $event['id_event'] ?>" 
                           class="btn btn-primary"><?= $lang['read_more'] ?? 'Lire plus' ?></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ((int)$eventsTotalPages > 1): ?>
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" id="events-prev"><?= $lang['previous'] ?? 'Précédent' ?></button>
                <div style="align-self: center; color: var(--text-light);">
                    <?= $lang['page'] ?? 'Page' ?> <span id="events-page-label"><?= (int)$eventsPage ?></span> / <span id="events-total-label"><?= (int)$eventsTotalPages ?></span>
                </div>
                <button type="button" class="btn btn-secondary" id="events-next"><?= $lang['next'] ?? 'Suivant' ?></button>
            </div>
            <?php endif; ?>
        </section>
        <?php
        return ob_get_clean();
    }
}
