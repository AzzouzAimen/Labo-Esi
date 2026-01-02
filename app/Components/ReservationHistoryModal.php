<?php
/**
 * ReservationHistoryModal Component
 * 
 * Modal overlay that displays reservation history for a specific equipment.
 * Shows user names, date ranges, conflict status badges, and urgent flags.
 * 
 * This component renders the modal container structure only.
 * The actual reservation data is loaded dynamically via JavaScript.
 * 
 * Optional props:
 * - baseUrl: string - Base URL for API calls
 */
class ReservationHistoryModal extends Component {
    public function render(): string {
        $baseUrl = $this->props['baseUrl'] ?? '';

        ob_start();
        ?>
        <!-- Reservation History Modal -->
        <div id="reservation-history-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
            <div class="modal-content" style="background: white; margin: 50px auto; max-width: 800px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <!-- Modal Header -->
                <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h2 id="modal-equipment-name" style="margin: 0; color: var(--primary-color);">Planning de réservation</h2>
                    <button onclick="closeReservationHistory()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-light);">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" style="padding: 1.5rem;">
                    <div id="reservation-list-container">
                        <div style="text-align: center; padding: 2rem; color: var(--text-light);">
                            Chargement...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Global variable to store equipment data
        window.equipmentData = <?= json_encode([]) ?>;

        // Show reservation history modal
        function showReservationHistory(equipId) {
            const modal = document.getElementById('reservation-history-modal');
            const container = document.getElementById('reservation-list-container');
            
            if (!modal || !container) return;
            
            modal.style.display = 'block';
            container.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-light);">Chargement...</div>';

            // Fetch reservation history via AJAX
            fetch('<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>index.php?controller=Equipment&action=getReservations&equip_id=' + equipId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.reservations) {
                        renderReservations(data.reservations, data.equipmentName);
                    } else {
                        container.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-light);">Aucune réservation trouvée.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching reservations:', error);
                    container.innerHTML = '<div style="text-align: center; padding: 2rem; color: #e74c3c;">Erreur lors du chargement des réservations.</div>';
                });
        }

        // Render reservations
        function renderReservations(reservations, equipmentName) {
            const container = document.getElementById('reservation-list-container');
            const nameHeader = document.getElementById('modal-equipment-name');
            
            if (nameHeader && equipmentName) {
                nameHeader.textContent = 'Planning - ' + equipmentName;
            }
            
            if (!reservations || reservations.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--text-light);">Aucune réservation trouvée.</div>';
                return;
            }

            let html = '<div style="display: flex; flex-direction: column; gap: 1rem;">';
            
            reservations.forEach(res => {
                const status = res.status || 'confirmed';
                const isUrgent = res.is_urgent == 1;
                const urgentReason = res.urgent_reason || '';
                
                // Status badge styling
                let statusBadge = '';
                let statusClass = 'badge-success';
                let statusText = 'Confirmée';
                
                if (status === 'conflict') {
                    statusClass = 'badge-danger';
                    statusText = 'Conflit';
                } else if (status === 'pending') {
                    statusClass = 'badge-warning';
                    statusText = 'En attente';
                } else if (status === 'rejected') {
                    statusClass = 'badge-secondary';
                    statusText = 'Rejetée';
                } else if (status === 'finished') {
                    statusClass = 'badge-info';
                    statusText = 'Terminée';
                }
                
                statusBadge = `<span class="badge ${statusClass}">${statusText}</span>`;
                
                // Urgent badge
                const urgentBadge = isUrgent ? '<span class="badge badge-danger" style="margin-left: 0.5rem;">URGENT</span>' : '';
                
                html += `
                    <div style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 4px; background: var(--bg-light);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <strong style="color: var(--primary-color);">${res.user_name || 'Utilisateur inconnu'}</strong>
                            <div>${statusBadge}${urgentBadge}</div>
                        </div>
                        <div style="color: var(--text-light); font-size: 0.9rem;">
                            <div><strong>Début:</strong> ${formatDate(res.date_debut)}</div>
                            <div><strong>Fin:</strong> ${formatDate(res.date_fin)}</div>
                            ${res.motif ? `<div><strong>Motif:</strong> ${res.motif}</div>` : ''}
                            ${isUrgent && urgentReason ? `<div style="margin-top: 0.5rem; padding: 0.5rem; background: #fff3cd; border-left: 3px solid #ffc107;"><strong>Raison de l'urgence:</strong> ${urgentReason}</div>` : ''}
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        // Close modal
        function closeReservationHistory() {
            const modal = document.getElementById('reservation-history-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Format datetime for display
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString('fr-FR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reservation-history-modal');
            if (event.target === modal) {
                closeReservationHistory();
            }
        }
        </script>
        <?php
        return ob_get_clean();
    }
}
