<?php
/**
 * PageHeader Component
 * 
 * Renders a page title with optional back button and breadcrumb
 */
class PageHeader extends Component {
    /**
     * Render the page header HTML
     * 
     * Expected props:
     * - title: String page title (required)
     * - backUrl: String URL for back button (optional)
     * - backText: String text for back button (optional, default: 'Retour')
     * - subtitle: String subtitle text (optional)
     * - actions: Array of action buttons (optional)
     *   - ['label' => 'Text', 'url' => '...', 'class' => 'btn-primary']
     * 
     * @return string The rendered HTML
     */
    public function render(): string {
        $title = $this->props['title'] ?? '';
        $backUrl = $this->props['backUrl'] ?? null;
        $backText = $this->props['backText'] ?? 'Retour';
        $subtitle = $this->props['subtitle'] ?? null;
        $actions = $this->props['actions'] ?? [];
        
        ob_start();
        ?>
        <!-- Page Header -->
        <?php if ($backUrl): ?>
        <div style="margin-bottom: 2rem;">
            <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">
                ◀ <?= htmlspecialchars($backText, ENT_QUOTES, 'UTF-8') ?>
            </a>
        </div>
        <?php endif; ?>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1 class="section-title" style="margin-bottom: <?= $subtitle ? '0.5rem' : '0' ?>;">
                    <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <?php if ($subtitle): ?>
                <p style="color: var(--text-light); font-size: 1.1rem; margin-top: 0.5rem;">
                    <?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?>
                </p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($actions)): ?>
            <div style="display: flex; gap: 1rem;">
                <?php foreach ($actions as $action): ?>
                <a href="<?= htmlspecialchars($action['url'] ?? '#', ENT_QUOTES, 'UTF-8') ?>" 
                   class="btn <?= htmlspecialchars($action['class'] ?? 'btn-primary', ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($action['label'] ?? 'Action', ENT_QUOTES, 'UTF-8') ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
