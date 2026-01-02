<?php
/**
 * MemberCard Component
 * 
 * Displays a single team member card with photo, name, grade, role, and action buttons.
 * 
 * Required props:
 * - member: array with member data (id_user, prenom, nom, grade, photo, role_dans_equipe, email)
 * - lang: array with translations
 * - baseUrl: string base URL
 * 
 * Optional props:
 * - isLeader: bool - Whether this member is a team leader (changes styling)
 * - showEmail: bool - Whether to show email contact button (default: true)
 */
class MemberCard extends Component {
    public function render(): string {
        $member = $this->props['member'] ?? [];
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? '';
        $isLeader = $this->props['isLeader'] ?? false;
        $showEmail = $this->props['showEmail'] ?? true;

        if (empty($member)) {
            return '';
        }

        $userId = (int)($member['id_user'] ?? 0);
        $prenom = htmlspecialchars($member['prenom'] ?? '', ENT_QUOTES, 'UTF-8');
        $nom = htmlspecialchars($member['nom'] ?? '', ENT_QUOTES, 'UTF-8');
        $fullName = trim($prenom . ' ' . $nom);
        $grade = htmlspecialchars($member['grade'] ?? '', ENT_QUOTES, 'UTF-8');
        $photo = htmlspecialchars($member['photo'] ?? '', ENT_QUOTES, 'UTF-8');
        $role = htmlspecialchars($member['role_dans_equipe'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($member['email'] ?? '', ENT_QUOTES, 'UTF-8');

        // Initials for placeholder
        $initials = '';
        if (!empty($prenom) && !empty($nom)) {
            $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        }

        ob_start();
        ?>
        <div class="member-card"
             data-grade="<?= $grade ?>"
             data-name="<?= $fullName ?>"
             style="<?= $isLeader ? 'border: 2px solid var(--secondary-color);' : '' ?>">
            
            <?php if ($isLeader): ?>
                <div style="background: var(--secondary-color); color: white; text-align: center; padding: 0.5rem; font-size: 0.9rem; font-weight: bold; margin-bottom: 1rem;">
                    <?= htmlspecialchars($lang['team_leader'] ?? 'Chef d\'équipe', ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($photo)): ?>
                <img src="<?= $baseUrl . $photo ?>" 
                     alt="<?= $fullName ?>"
                     class="member-photo"
                     onerror="this.src='<?= $baseUrl ?>assets/img/user-placeholder.jpg'">
            <?php else: ?>
                <div style="width: 120px; height: 120px; background: var(--secondary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem; border: 3px solid var(--secondary-color);">
                    <?= $initials ?>
                </div>
            <?php endif; ?>
            
            <div class="member-name">
                <?= $fullName ?>
            </div>
            
            <div class="member-grade">
                <?= $grade ?>
            </div>
            
            <?php if (!empty($role)): ?>
                <div style="margin-top: 0.5rem;">
                    <span class="badge badge-primary">
                        <?= $role ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="<?= $baseUrl ?>index.php?controller=Team&action=profile&id=<?= $userId ?>" 
                   class="btn btn-primary" 
                   style="font-size: 0.9rem; padding: 0.5rem 1rem; width: 100%;">
                    <?= htmlspecialchars($lang['view_details'] ?? 'Voir détails', ENT_QUOTES, 'UTF-8') ?>
                </a>
                
                <?php if ($showEmail && !empty($email)): ?>
                    <a href="mailto:<?= $email ?>" 
                       class="btn btn-secondary" 
                       style="font-size: 0.9rem; padding: 0.5rem 1rem; width: 100%;">
                        Contact
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
