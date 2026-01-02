<?php
/**
 * DashboardStat Component
 * 
 * Displays a counter/statistic widget with label, value, and optional icon.
 * Used for dashboard summary cards like "My Projects: 5", "My Publications: 12", etc.
 * 
 * Required props:
 * - label: string - Display label (e.g., "My Projects")
 * - value: int|string - Statistic value (e.g., 5)
 * 
 * Optional props:
 * - icon: string - Icon class or symbol (e.g., "📊")
 * - color: string - CSS color for accent (default: var(--primary-color))
 * - href: string - Link URL if stat is clickable
 */
class DashboardStat extends Component {
    public function render(): string {
        $label = $this->props['label'] ?? '';
        $value = $this->props['value'] ?? 0;
        $icon = $this->props['icon'] ?? '';
        $color = $this->props['color'] ?? 'var(--primary-color)';
        $href = $this->props['href'] ?? '';

        if (empty($label)) {
            return '';
        }

        $labelEscaped = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $valueEscaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $iconEscaped = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');
        $colorEscaped = htmlspecialchars($color, ENT_QUOTES, 'UTF-8');

        ob_start();
        ?>
        <div class="dashboard-stat" style="background: white; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; <?= !empty($href) ? 'cursor: pointer; transition: box-shadow 0.2s;' : '' ?>" <?= !empty($href) ? 'onclick="window.location.href=\'' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '\'"' : '' ?>>
            <?php if (!empty($icon)): ?>
                <div style="font-size: 2rem; color: <?= $colorEscaped ?>;">
                    <?= $iconEscaped ?>
                </div>
            <?php endif; ?>
            
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                <span style="color: var(--text-light); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em;">
                    <?= $labelEscaped ?>
                </span>
                <span style="font-size: 2rem; font-weight: bold; color: <?= $colorEscaped ?>;">
                    <?= $valueEscaped ?>
                </span>
            </div>
        </div>

        <?php if (!empty($href)): ?>
        <style>
        .dashboard-stat:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        </style>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }
}
