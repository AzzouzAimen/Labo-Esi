<?php
/**
 * Alert Component
 * 
 * Renders success/error/info/warning message banners
 */
class Alert extends Component {
    /**
     * Render the alert HTML
     * 
     * Expected props:
     * - message: String message to display (required)
     * - type: String alert type: 'success', 'error', 'warning', 'info' (default: 'info')
     * - dismissible: Boolean whether alert can be closed (default: false)
     * 
     * @return string The rendered HTML
     */
    public function render(): string {
        $message = $this->props['message'] ?? '';
        $type = $this->props['type'] ?? 'info';
        $dismissible = $this->props['dismissible'] ?? false;
        
        if (empty($message)) {
            return '';
        }
        
        // Define alert styles
        $styles = [
            'success' => [
                'bg' => '#d4edda',
                'border' => '#c3e6cb',
                'color' => '#155724'
            ],
            'error' => [
                'bg' => '#f8d7da',
                'border' => '#f5c6cb',
                'color' => '#721c24'
            ],
            'warning' => [
                'bg' => '#fff3cd',
                'border' => '#ffeaa7',
                'color' => '#856404'
            ],
            'info' => [
                'bg' => '#d1ecf1',
                'border' => '#bee5eb',
                'color' => '#0c5460'
            ]
        ];
        
        $style = $styles[$type] ?? $styles['info'];
        
        ob_start();
        ?>
        <div class="alert alert-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" 
             style="background: <?= $style['bg'] ?>; border: 1px solid <?= $style['border'] ?>; color: <?= $style['color'] ?>; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; position: relative;"
             <?php if ($dismissible): ?>data-dismissible="true"<?php endif; ?>>
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            <?php if ($dismissible): ?>
            <button type="button" onclick="this.parentElement.remove()" 
                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 1.5rem; cursor: pointer; color: inherit; opacity: 0.7;">
                ×
            </button>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
