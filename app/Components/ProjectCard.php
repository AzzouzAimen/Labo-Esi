<?php
/**
 * ProjectCard Component
 * 
 * Renders a single project card with image, metadata, status badge, and action buttons
 */
class ProjectCard extends Component {
    /**
     * Render the project card HTML
     * 
     * Expected props:
     * - project: Array project data (required)
     * - lang: Array of language strings
     * - baseUrl: String base URL
     * 
     * @return string The rendered HTML
     */
    public function render(): string {
        $project = $this->props['project'] ?? [];
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? BASE_URL;
        
        if (empty($project)) {
            return '';
        }
        
        // Determine status badge class
        $statusClass = 'badge-primary';
        $status = strtolower($project['statut'] ?? '');
        
        if ($status === 'terminé' || $status === 'completed') {
            $statusClass = 'badge-success';
        } elseif ($status === 'soumis' || $status === 'submitted') {
            $statusClass = 'badge-warning';
        } elseif ($status === 'en cours' || $status === 'ongoing') {
            $statusClass = 'badge-primary';
        }
        
        $imageUrl = !empty($project['image_url']) 
            ? $baseUrl . htmlspecialchars($project['image_url'], ENT_QUOTES, 'UTF-8')
            : $baseUrl . 'assets/img/project-placeholder.jpg';
        
        ob_start();
        ?>
        <div class="card" data-domain="<?= htmlspecialchars($project['domaine'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
             data-status="<?= htmlspecialchars($project['statut'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
             data-supervisor="<?= (int)($project['id_responsable'] ?? 0) ?>">
            <img src="<?= $imageUrl ?>" 
                 alt="<?= htmlspecialchars($project['titre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                 onerror="this.src='<?= $baseUrl ?>assets/img/project-placeholder.jpg'">
            
            <div class="card-body">
                <h3 class="card-title"><?= htmlspecialchars($project['titre'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
                
                <div class="card-meta">
                    <span>
                        <strong><?= $lang['project_domain'] ?? 'Domaine' ?>:</strong> 
                        <?= htmlspecialchars($project['domaine'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    
                    <?php if (!empty($project['responsable_nom'])): ?>
                    <span>
                        <strong><?= $lang['project_manager'] ?? 'Responsable' ?>:</strong> 
                        <?= htmlspecialchars(($project['responsable_prenom'] ?? '') . ' ' . ($project['responsable_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <?php endif; ?>
                    
                    <span>
                        <strong><?= $lang['project_associated_members'] ?? 'Membres associés' ?>:</strong> 
                        <?= !empty($project['membres_noms']) 
                            ? htmlspecialchars($project['membres_noms'], ENT_QUOTES, 'UTF-8') 
                            : ($lang['none'] ?? 'Aucun') ?>
                    </span>
                    
                    <span>
                        <strong><?= $lang['project_funding'] ?? 'Financement' ?>:</strong>
                        <?= !empty($project['type_financement']) 
                            ? htmlspecialchars($project['type_financement'], ENT_QUOTES, 'UTF-8') 
                            : 'N/A' ?>
                    </span>
                    
                    <span>
                        <strong><?= $lang['project_members'] ?? 'Membres' ?>:</strong>
                        <?= isset($project['membres_count']) ? (int)$project['membres_count'] : 0 ?>
                    </span>
                </div>
                
                <div class="mb-2">
                    <span class="badge <?= $statusClass ?>">
                        <?= htmlspecialchars($project['statut'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                
                <div style="margin-top: auto;">
                    <a href="<?= $baseUrl ?>index.php?controller=Project&action=detail&id=<?= (int)($project['id_project'] ?? 0) ?>" 
                       class="btn btn-primary" style="display: block; width: 100%; margin-bottom: 0.5rem;">
                        <?= $lang['view_details'] ?? 'Voir les détails' ?>
                    </a>
                    <a href="<?= $baseUrl ?>index.php?controller=Publication&action=index&project=<?= (int)($project['id_project'] ?? 0) ?>" 
                       class="btn btn-outline-primary" style="display: block; width: 100%;">
                        <?= $lang['view_publications'] ?? 'Voir les publications' ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
