<?php
/**
 * ReservationForm Component
 * 
 * Renders an equipment reservation form with support for urgent reservations.
 * Features a checkbox for urgent reservations that conditionally shows a reason textarea.
 * 
 * Required props:
 * - equipId: int - Equipment ID to reserve
 * - baseUrl: string - Base URL
 * 
 * Optional props:
 * - lang: array - Translations
 */
class ReservationForm extends Component {
    public function render(): string {
        $equipId = (int)($this->props['equipId'] ?? 0);
        $baseUrl = $this->props['baseUrl'] ?? '';
        $lang = $this->props['lang'] ?? [];

        if (!$equipId) {
            return '';
        }

        // Unique form ID to handle multiple forms on the same page
        $formId = 'reservation-form-' . $equipId;
        $urgentCheckId = 'urgent-check-' . $equipId;
        $urgentReasonId = 'urgent-reason-' . $equipId;

        ob_start();
        ?>
        <form method="POST" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>index.php?controller=Equipment&action=reserve" id="<?= $formId ?>" style="display: grid; gap: 0.5rem;">
            <input type="hidden" name="equip_id" value="<?= $equipId ?>">
            
            <!-- Date/Time Inputs -->
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <div>
                    <label style="display:block; font-size:0.9rem; color: var(--text-light);">Début</label>
                    <input required type="datetime-local" name="date_debut" class="form-control">
                </div>
                <div>
                    <label style="display:block; font-size:0.9rem; color: var(--text-light);">Fin</label>
                    <input required type="datetime-local" name="date_fin" class="form-control">
                </div>
            </div>

            <!-- Motif -->
            <div>
                <label style="display:block; font-size:0.9rem; color: var(--text-light);">Motif (optionnel)</label>
                <input type="text" name="motif" class="form-control" placeholder="Ex: Expérience, TP, test...">
            </div>

            <!-- Urgent Checkbox -->
            <div style="margin-top: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input 
                        type="checkbox" 
                        id="<?= $urgentCheckId ?>" 
                        name="is_urgent" 
                        value="1"
                        onchange="toggleUrgentReason('<?= $urgentReasonId ?>', this.checked)"
                        style="width: auto;">
                    <span style="font-size: 0.9rem;">Réservation urgente</span>
                </label>
            </div>

            <!-- Conditional Urgent Reason (Hidden by default) -->
            <div id="<?= $urgentReasonId ?>" style="display: none;">
                <label style="display:block; font-size:0.9rem; color: var(--text-light);">
                    Raison de l'urgence <span style="color: #e74c3c;">*</span>
                </label>
                <textarea 
                    name="urgent_reason" 
                    class="form-control" 
                    rows="3" 
                    placeholder="Expliquez pourquoi cette réservation est urgente..."></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">
                Réserver
            </button>
        </form>

        <script>
        // Toggle urgent reason visibility
        function toggleUrgentReason(elementId, isVisible) {
            const element = document.getElementById(elementId);
            const textarea = element ? element.querySelector('textarea') : null;
            
            if (element) {
                element.style.display = isVisible ? 'block' : 'none';
            }
            
            if (textarea) {
                // Make required only when visible
                if (isVisible) {
                    textarea.setAttribute('required', 'required');
                } else {
                    textarea.removeAttribute('required');
                    textarea.value = ''; // Clear value when hidden
                }
            }
        }
        </script>
        <?php
        return ob_get_clean();
    }
}
