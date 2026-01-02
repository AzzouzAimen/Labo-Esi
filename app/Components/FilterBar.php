<?php
/**
 * FilterBar Component
 * 
 * Renders a flexible filter bar with selects, inputs, and buttons
 */
class FilterBar extends Component {
    /**
     * Render the filter bar HTML
     * 
     * Expected props:
     * - filters: Array of filter definitions (required)
     *   Each filter: [
     *     'type' => 'select' | 'text' | 'date',
     *     'id' => 'filter-domain',
     *     'label' => 'Filter by Domain',
     *     'options' => [...] // For select only
     *     'placeholder' => '...' // For text/date
     *   ]
     * - lang: Array of language strings (optional)
     * 
     * @return string The rendered HTML
     */
    public function render(): string {
        $filters = $this->props['filters'] ?? [];
        $lang = $this->props['lang'] ?? [];
        
        if (empty($filters)) {
            return '';
        }
        
        ob_start();
        ?>
        <!-- Filter Bar -->
        <div class="filter-bar" style="background: var(--bg-light); padding: 1.5rem; margin-bottom: 2rem; border: 1px solid var(--border-color); display: flex; gap: 2rem; flex-wrap: wrap; align-items: flex-end;">
            <?php foreach ($filters as $filter): ?>
                <?php
                $type = $filter['type'] ?? 'select';
                $id = $filter['id'] ?? 'filter-' . uniqid();
                $label = $filter['label'] ?? '';
                $name = $filter['name'] ?? $id;
                ?>
                
                <div style="flex: 1; min-width: 200px;">
                    <?php if ($label): ?>
                    <label for="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>:
                    </label>
                    <?php endif; ?>
                    
                    <?php if ($type === 'select'): ?>
                        <select id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" 
                                name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                                class="form-control" 
                                style="width: 100%;">
                            <option value="all"><?= htmlspecialchars($lang['all'] ?? 'Tous', ENT_QUOTES, 'UTF-8') ?></option>
                            <?php if (!empty($filter['options'])): ?>
                                <?php foreach ($filter['options'] as $option): ?>
                                    <?php
                                    $value = is_array($option) ? ($option['value'] ?? '') : $option;
                                    $text = is_array($option) ? ($option['text'] ?? $value) : $option;
                                    ?>
                                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        
                    <?php elseif ($type === 'text'): ?>
                        <input type="text" 
                               id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" 
                               name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                               class="form-control"
                               placeholder="<?= htmlspecialchars($filter['placeholder'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               style="width: 100%;">
                               
                    <?php elseif ($type === 'date'): ?>
                        <input type="date" 
                               id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" 
                               name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                               class="form-control"
                               style="width: 100%;">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <?php if (!empty($this->props['showResetButton'])): ?>
            <div style="flex: 0;">
                <button type="button" class="btn btn-secondary" onclick="document.querySelectorAll('.filter-bar select, .filter-bar input').forEach(el => { if(el.tagName === 'SELECT') el.value = 'all'; else el.value = ''; }); if(typeof applyFilters === 'function') applyFilters();">
                    <?= htmlspecialchars($lang['reset'] ?? 'Réinitialiser', ENT_QUOTES, 'UTF-8') ?>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
