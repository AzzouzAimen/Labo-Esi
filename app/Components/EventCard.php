<?php

require_once __DIR__ . '/../../core/Component.php';

/**
 * EventCard Component
 * 
 * Displays event details in a split layout with image/description on left
 * and metadata sidebar on right.
 * 
 * Props:
 * - event (required): Event data array with keys: titre, description, image_url, date_event, lieu, type
 * - lang (required): Language array
 * - baseUrl (required): Base URL for links
 */
class EventCard extends Component {
    
    public function render(): string {
        $event = $this->props['event'];
        $lang = $this->props['lang'];
        $baseUrl = $this->props['baseUrl'];
        
        // Extract and validate event data
        $titre = $event['titre'] ?? '';
        $description = $event['description'] ?? '';
        $imageUrl = $event['image_url'] ?? '';
        $dateEvent = $event['date_event'] ?? '';
        $lieu = $event['lieu'] ?? '';
        $type = $event['type'] ?? '';
        
        // Format date
        $formattedDate = 'N/A';
        if (!empty($dateEvent)) {
            try {
                $formattedDate = date('d/m/Y H:i', strtotime($dateEvent));
            } catch (Exception $e) {
                $formattedDate = 'N/A';
            }
        }
        
        ob_start();
        ?>
        <div class="event-card-detail" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
            <!-- Left Column: Image + Description -->
            <div>
                <?php if (!empty($imageUrl)): ?>
                    <img src="<?= $baseUrl . $this->escape($imageUrl) ?>" 
                         alt="<?= $this->escape($titre) ?>" 
                         style="width: 100%; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" 
                         onerror="this.src='<?= $baseUrl ?>assets/img/event-placeholder.jpg'">
                <?php endif; ?>

                <div style="background: var(--bg-light); padding: 2rem; border-radius: 8px;">
                    <h2 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.5rem;">
                        <?= $lang['event_description'] ?? 'Description' ?>
                    </h2>
                    <p style="line-height: 1.8; margin: 0; color: var(--text-color);">
                        <?php if (!empty($description)): ?>
                            <?= nl2br($this->escape($description)) ?>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-style: italic;">
                                <?= $lang['no_description'] ?? 'Aucune description disponible.' ?>
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Right Column: Metadata Sidebar -->
            <div>
                <div style="background: white; border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <h3 style="color: var(--primary-color); margin-bottom: 1.5rem; font-size: 1.2rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                        <?= $lang['event_info'] ?? 'Informations' ?>
                    </h3>

                    <!-- Date -->
                    <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem;">
                            <span style="font-size: 1.2rem;">📅</span>
                            <strong style="color: var(--text-light);"><?= $lang['event_date'] ?? 'Date' ?>:</strong>
                        </div>
                        <div style="margin-left: 1.8rem; color: var(--text-color);">
                            <?= $this->escape($formattedDate) ?>
                        </div>
                    </div>

                    <!-- Location -->
                    <?php if (!empty($lieu)): ?>
                    <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem;">
                            <span style="font-size: 1.2rem;">📍</span>
                            <strong style="color: var(--text-light);"><?= $lang['event_location'] ?? 'Lieu' ?>:</strong>
                        </div>
                        <div style="margin-left: 1.8rem; color: var(--text-color);">
                            <?= $this->escape($lieu) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Type -->
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem;">
                            <span style="font-size: 1.2rem;">🏷️</span>
                            <strong style="color: var(--text-light);"><?= $lang['event_type'] ?? 'Type' ?>:</strong>
                        </div>
                        <div style="margin-left: 1.8rem;">
                            <span class="badge badge-primary" style="font-size: 0.9rem;">
                                <?= $this->escape($type) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
