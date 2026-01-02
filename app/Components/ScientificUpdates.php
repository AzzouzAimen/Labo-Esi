<?php
/**
 * ScientificUpdates Component
 * 
 * Displays recent projects, publications, and collaborations
 */
class ScientificUpdates extends Component {
    /**
     * Render the scientific updates HTML
     * 
     * Expected props:
     * - recentProjects: Array of recent projects
     * - recentPublications: Array of recent publications
     * - recentPartners: Array of recent partners
     * - lang: Array of language strings
     * - baseUrl: String base URL
     * 
     * @return string The rendered HTML
     */
    public function render(): string {
        $recentProjects = $this->props['recentProjects'] ?? [];
        $recentPublications = $this->props['recentPublications'] ?? [];
        $recentPartners = $this->props['recentPartners'] ?? [];
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? BASE_URL;
        
        ob_start();
        ?>
        <!-- Scientific Updates Section -->
        <section class="scientific-updates mt-4">
            <h2 class="section-title"><?= $lang['section_scientific_news'] ?? 'Actualités Scientifiques' ?></h2>
            <div class="updates-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                
                <!-- Recent Projects -->
                <div class="update-column">
                    <h3 style="font-size: 1.3rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                        <?= $lang['recent_projects'] ?? 'Projets Récents' ?>
                    </h3>
                    <?php if (!empty($recentProjects)): ?>
                        <div class="updates-cards">
                            <?php foreach ($recentProjects as $project): ?>
                            <div class="update-card">
                                <h4 class="update-card-title">
                                    <a href="<?= $baseUrl ?>index.php?controller=Project&action=detail&id=<?= $project['id_project'] ?>">
                                        <?= htmlspecialchars($project['titre'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h4>
                                <span class="badge badge-secondary"><?= htmlspecialchars($project['domaine'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><?= $lang['no_recent_projects'] ?? 'Aucun projet récent' ?></p>
                    <?php endif; ?>
                    <a href="<?= $baseUrl ?>index.php?controller=Project&action=index" class="btn btn-sm btn-outline-primary"><?= $lang['view_all_projects'] ?? 'Tous les projets' ?></a>
                </div>

                <!-- Recent Publications -->
                <div class="update-column">
                    <h3 style="font-size: 1.3rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                        <?= $lang['recent_publications'] ?? 'Publications Récentes' ?>
                    </h3>
                    <?php if (!empty($recentPublications)): ?>
                        <div class="updates-cards">
                            <?php foreach ($recentPublications as $pub): ?>
                            <div class="update-card">
                                <p class="update-card-title">
                                    <?= htmlspecialchars($pub['titre'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <small class="update-card-meta"><?= $lang['year_label'] ?? 'Année' ?>: <?= htmlspecialchars($pub['annee'], ENT_QUOTES, 'UTF-8') ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><?= $lang['no_recent_publications'] ?? 'Aucune publication récente' ?></p>
                    <?php endif; ?>
                    <a href="<?= $baseUrl ?>index.php?controller=Publication&action=index" class="btn btn-sm btn-outline-primary"><?= $lang['view_all_publications'] ?? 'Toutes les publications' ?></a>
                </div>

                <!-- Collaborations -->
                <div class="update-column">
                    <h3 style="font-size: 1.3rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                        <?= $lang['collaborations'] ?? 'Collaborations' ?>
                    </h3>
                    <?php if (!empty($recentPartners)): ?>
                        <div class="updates-cards">
                            <?php foreach ($recentPartners as $partner): ?>
                            <div class="update-card collaboration-card">
                                <?php if ($partner['logo_url']): ?>
                                    <img src="<?= $baseUrl . htmlspecialchars($partner['logo_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($partner['nom'], ENT_QUOTES, 'UTF-8') ?>" class="collab-logo">
                                <?php endif; ?>
                                <div class="collab-info">
                                    <h4 class="update-card-title"><?= htmlspecialchars($partner['nom'], ENT_QUOTES, 'UTF-8') ?></h4>
                                    <small class="update-card-meta"><?= htmlspecialchars($partner['type'], ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><?= $lang['no_new_collaborations'] ?? 'Aucune nouvelle collaboration' ?></p>
                    <?php endif; ?>
                    <a href="#partners-section" class="btn btn-sm btn-outline-primary scroll-to-section"><?= $lang['view_partners'] ?? 'Voir partenaires' ?></a>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
