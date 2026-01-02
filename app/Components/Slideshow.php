<?php
/**
 * Slideshow Component
 * 
 * Renders a slideshow of recent news/events
 */
class Slideshow extends Component {
    /**
     * Render the slideshow HTML
     * 
     * Expected props:
     * - recentNews: Array of news items
     * - lang: Array of language strings
     * - baseUrl: String base URL
     * 
     * @return string The rendered slideshow HTML
     */
    public function render(): string {
        $recentNews = $this->props['recentNews'] ?? [];
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? BASE_URL;
        
        if (empty($recentNews)) {
            return '';
        }
        
        ob_start();
        ?>
        <!-- Slideshow Section -->
        <section class="diapo-div">
            <div class="diapo">
                <?php foreach ($recentNews as $index => $news): ?>
                    <?php if ($index > 0): ?>
                    <div class="separator"></div>
                    <?php endif; ?>
                    
                    <div class="slide-item">
                        <?php if ($news['image_url']): ?>
                            <img src="<?= $baseUrl . htmlspecialchars($news['image_url'], ENT_QUOTES, 'UTF-8') ?>" 
                                 alt="<?= htmlspecialchars($news['titre'], ENT_QUOTES, 'UTF-8') ?>"
                                 onerror="this.src='<?= $baseUrl ?>assets/img/news-placeholder.jpg'">
                        <?php else: ?>
                            <img src="<?= $baseUrl ?>assets/img/news-placeholder.jpg" alt="<?= htmlspecialchars($news['titre'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>
                        
                        <div class="slide-overlay">
                            <div class="slide-content">
                                <h2><?= htmlspecialchars($news['titre'], ENT_QUOTES, 'UTF-8') ?></h2>
                                <p><?= htmlspecialchars(substr($news['description'], 0, 150), ENT_QUOTES, 'UTF-8') ?>...</p>
                                <a href="<?= $baseUrl ?>index.php?controller=Event&action=detail&id=<?= $news['id_event'] ?>" 
                                   class="btn btn-primary"><?= $lang['view_details'] ?? 'Voir détails' ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
