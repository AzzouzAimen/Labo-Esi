<?php
/**
 * Partners Component
 * 
 * Displays a carousel of partner organizations
 */
class Partners extends Component {
    /**
     * Render the partners section HTML
     * 
     * Expected props:
     * - allPartners: Array of all partners
     * - lang: Array of language strings
     * - baseUrl: String base URL
     * 
     * @return string The rendered HTML
     */
    public function render(): string {
        $allPartners = $this->props['allPartners'] ?? [];
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? BASE_URL;
        
        ob_start();
        ?>
        <!-- Partners Section -->
        <section class="partners-section mt-4 mb-4" id="partners-section">
            <h2 class="section-title"><?= $lang['our_partners'] ?? 'Nos Partenaires' ?></h2>
            <div style="background: var(--bg-light); padding: 2rem;">
                <p style="color: var(--text-light); margin-bottom: 2rem; text-align: center;">
                    <?= $lang['partners_intro'] ?? 'Découvrez nos partenaires académiques et industriels' ?>
                </p>
                
                <?php if (!empty($allPartners)): ?>
                    <div class="partners-carousel-container">
                        <button type="button" class="carousel-arrow carousel-arrow-left" id="partners-prev">
                            ‹
                        </button>
                        
                        <div class="partners-carousel-mask">
                            <div class="partners-carousel" id="partners-carousel">
                                <?php foreach ($allPartners as $partner): ?>
                                    <div class="partner-carousel-item">
                                        <?php if ($partner['logo_url']): ?>
                                            <img src="<?= $baseUrl . htmlspecialchars($partner['logo_url'], ENT_QUOTES, 'UTF-8') ?>" 
                                                 alt="<?= htmlspecialchars($partner['nom'], ENT_QUOTES, 'UTF-8') ?>"
                                                 class="partner-logo">
                                        <?php else: ?>
                                            <div class="partner-badge">
                                                <?= htmlspecialchars($partner['nom'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                        <p class="partner-name"><?= htmlspecialchars($partner['nom'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="partner-type"><?= htmlspecialchars($partner['type'], ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <button type="button" class="carousel-arrow carousel-arrow-right" id="partners-next">
                            ›
                        </button>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem;">
                        <p style="color: var(--text-light);"><?= $lang['no_partners_available'] ?? 'Aucun partenaire disponible' ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
