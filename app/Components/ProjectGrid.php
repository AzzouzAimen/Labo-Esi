<?php
/**
 * ProjectGrid Component
 * 
 * Renders a grid of project cards with empty state handling
 */
class ProjectGrid extends Component {
    /**
     * Render the project grid HTML
     * 
     * Expected props:
     * - projects: Array of project data (required)
     * - lang: Array of language strings
     * - baseUrl: String base URL
     * - emptyMessage: String custom empty state message
     * 
     * @return string The rendered HTML
     */
    public function render(): string {
        $projects = $this->props['projects'] ?? [];
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? BASE_URL;
        $emptyMessage = $this->props['emptyMessage'] ?? ($lang['no_projects'] ?? 'Aucun projet trouvé');
        
        ob_start();
        ?>
        <!-- Projects Grid -->
        <div class="card-grid" id="projects-grid">
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $project): ?>
                    <?php
                    $card = new ProjectCard([
                        'project' => $project,
                        'lang' => $lang,
                        'baseUrl' => $baseUrl
                    ]);
                    echo $card->render();
                    ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: var(--bg-light); border: 1px dashed var(--border-color); color: var(--text-light); font-size: 1.1rem;">
                    <?= htmlspecialchars($emptyMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
