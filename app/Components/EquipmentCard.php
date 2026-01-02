<?php
/**
 * EquipmentCard Component
 * 
 * Displays a single equipment item with image, metadata, status badge,
 * and action buttons for viewing schedule and making reservations.
 * 
 * Required props:
 * - equipment: array with equipment data (id_equip, nom, reference, categorie, etat_actuel, description, image_url)
 * - lang: array with translations
 * - baseUrl: string base URL
 * 
 * Optional props:
 * - userId: int|null - Current user ID (shows reservation form if set)
 */
class EquipmentCard extends Component {
    public function render(): string {
        $equip = $this->props['equipment'] ?? [];
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? '';
        $userId = $this->props['userId'] ?? null;

        if (empty($equip)) {
            return '';
        }

        $equipId = (int)($equip['id_equip'] ?? 0);
        $nom = htmlspecialchars($equip['nom'] ?? '', ENT_QUOTES, 'UTF-8');
        $categorie = htmlspecialchars($equip['categorie'] ?? '', ENT_QUOTES, 'UTF-8');
        $reference = htmlspecialchars($equip['reference'] ?? '', ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($equip['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $imageUrl = htmlspecialchars($equip['image_url'] ?? '', ENT_QUOTES, 'UTF-8');
        $etat = $equip['etat_actuel'] ?? $equip['etat'] ?? 'libre';

        // Determine status badge
        $badge = 'badge-success';
        $label = $lang['status_free'] ?? 'Libre';
        if ($etat === 'réservé') {
            $badge = 'badge-warning';
            $label = $lang['status_reserved'] ?? 'Réservé';
        } elseif ($etat === 'maintenance') {
            $badge = 'badge-primary';
            $label = $lang['status_maintenance'] ?? 'Maintenance';
        }

        // Image source
        $imgSrc = !empty($imageUrl) ? $baseUrl . $imageUrl : $baseUrl . 'assets/img/project-placeholder.jpg';

        ob_start();
        ?>
        <div class="card">
            <img src="<?= $imgSrc ?>" alt="<?= $nom ?>" onerror="this.src='<?= $baseUrl ?>assets/img/project-placeholder.jpg'">

            <div class="card-body">
                <h3 class="card-title"><?= $nom ?></h3>

                <div class="card-meta">
                    <span><strong><?= htmlspecialchars($lang['equip_category'] ?? 'Catégorie', ENT_QUOTES, 'UTF-8') ?>:</strong> <?= $categorie ?></span>
                    <?php if (!empty($reference)): ?>
                        <span><strong>Réf:</strong> <?= $reference ?></span>
                    <?php endif; ?>
                </div>

                <div class="mb-2">
                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <?php if (!empty($description)): ?>
                    <p class="card-text"><?= substr($description, 0, 140) ?>...</p>
                <?php endif; ?>

                <!-- View Planning Button -->
                <div style="margin-bottom: 1rem;">
                    <button 
                        type="button" 
                        class="btn btn-secondary" 
                        onclick="showReservationHistory(<?= $equipId ?>)"
                        style="width: 100%;">
                        Voir Planning
                    </button>
                </div>

                <?php if ($userId): ?>
                    <!-- Reservation Form -->
                    <div style="margin-top: 1rem;">
                        <h4 style="font-size: 1rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <?= htmlspecialchars($lang['equip_reserve'] ?? 'Réserver', ENT_QUOTES, 'UTF-8') ?>
                        </h4>
                        <?php
                        // Render ReservationForm component inline
                        $reservationForm = new ReservationForm([
                            'equipId' => $equipId,
                            'baseUrl' => $baseUrl,
                            'lang' => $lang
                        ]);
                        echo $reservationForm->render();
                        ?>
                    </div>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>index.php?controller=Auth&action=login" class="btn btn-primary">
                        Se connecter pour réserver
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
