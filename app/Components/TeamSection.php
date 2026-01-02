<?php
/**
 * TeamSection Component
 * 
 * Displays a complete team section with header, leader, and members grid.
 * Groups team members and renders them using MemberCard components.
 * 
 * Required props:
 * - team: array with team data (id_team, nom, description, chef_id, members array)
 * - lang: array with translations
 * - baseUrl: string base URL
 * 
 * Optional props:
 * - showPublicationsLink: bool - Whether to show "View team publications" link (default: true)
 */
class TeamSection extends Component {
    public function render(): string {
        $team = $this->props['team'] ?? [];
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? '';
        $showPublicationsLink = $this->props['showPublicationsLink'] ?? true;

        if (empty($team)) {
            return '';
        }

        $teamId = (int)($team['id_team'] ?? 0);
        $nom = htmlspecialchars($team['nom'] ?? '', ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($team['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $chefId = (int)($team['chef_id'] ?? 0);
        $members = $team['members'] ?? [];

        // Extract leader data if exists
        $leader = null;
        if ($chefId) {
            $leader = [
                'id_user' => $chefId,
                'prenom' => $team['chef_prenom'] ?? '',
                'nom' => $team['chef_nom'] ?? '',
                'grade' => $team['chef_grade'] ?? '',
                'photo' => $team['chef_photo'] ?? '',
                'email' => $team['chef_email'] ?? '',
                'role_dans_equipe' => 'Chef d\'équipe'
            ];
        }

        ob_start();
        ?>
        <div class="team-section" data-team-id="<?= $teamId ?>">
            <!-- Team Header -->
            <div class="team-header">
                <h2><?= $nom ?></h2>
                <?php if (!empty($description)): ?>
                    <p style="opacity: 0.9; margin-top: 0.5rem;">
                        <?= $description ?>
                    </p>
                <?php endif; ?>

                <?php if ($showPublicationsLink): ?>
                    <div style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="<?= $baseUrl ?>index.php?controller=Publication&action=index&team_id=<?= $teamId ?>" 
                           class="btn btn-secondary">
                            Voir les publications de l'équipe
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Team Leader -->
            <?php if ($leader): ?>
                <div class="team-leader">
                    <?php
                    $leaderCard = new MemberCard([
                        'member' => $leader,
                        'lang' => $lang,
                        'baseUrl' => $baseUrl,
                        'isLeader' => true,
                        'showEmail' => true
                    ]);
                    echo $leaderCard->render();
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Team Members -->
            <?php if (!empty($members)): ?>
                <div style="margin-top: 2rem;">
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.2rem;">
                        <?= htmlspecialchars($lang['team_members'] ?? 'Membres de l\'équipe', ENT_QUOTES, 'UTF-8') ?>
                    </h3>
                    <div class="team-members">
                        <?php foreach ($members as $member): ?>
                            <?php
                            $memberCard = new MemberCard([
                                'member' => $member,
                                'lang' => $lang,
                                'baseUrl' => $baseUrl,
                                'isLeader' => false,
                                'showEmail' => false
                            ]);
                            echo $memberCard->render();
                            ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
