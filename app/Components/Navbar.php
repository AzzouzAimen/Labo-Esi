<?php
/**
 * Navbar Component
 * 
 * Renders the main navigation menu with user authentication state
 */
class Navbar extends Component {
    /**
     * Render the navbar HTML
     * 
     * Expected props:
     * - lang: Array of language strings
     * - baseUrl: String base URL for links
     * - userId: Integer|null current user ID (from session)
     * 
     * @return string The rendered navbar HTML
     */
    public function render(): string {
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? BASE_URL;
        $userId = $this->props['userId'] ?? null;

        ob_start();
        ?>
        <!-- Navigation -->
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="<?= $baseUrl ?>index.php?controller=Home&action=index"><?= $lang['nav_home'] ?></a></li>
                    <li><a href="<?= $baseUrl ?>index.php?controller=Team&action=presentation"><?= $lang['nav_presentation'] ?? 'Présentation' ?></a></li>
                    <li><a href="<?= $baseUrl ?>index.php?controller=Project&action=index"><?= $lang['nav_projects'] ?></a></li>
                    <li><a href="<?= $baseUrl ?>index.php?controller=Publication&action=index"><?= $lang['nav_publications'] ?></a></li>
                    <li><a href="<?= $baseUrl ?>index.php?controller=Equipment&action=index"><?= $lang['nav_equipment'] ?></a></li>
                    <li><a href="<?= $baseUrl ?>index.php?controller=Offer&action=index"><?= $lang['nav_offers'] ?? 'Offres' ?></a></li>
                    <li><a href="<?= $baseUrl ?>index.php?controller=Team&action=membres"><?= $lang['nav_members'] ?? 'Membres' ?></a></li>
                    <li><a href="<?= $baseUrl ?>index.php?controller=Home&action=contact"><?= $lang['nav_contact'] ?></a></li>
                    <?php if ($userId): ?>
                        <li class="user-menu">
                            <a href="<?= $baseUrl ?>index.php?controller=Dashboard&action=index"><?= $lang['dashboard'] ?></a>
                        </li>
                        <li class="notification-item" style="display: flex; align-items: center;">
                            <a href="#" style="padding: 0.5rem 1rem; display: flex; align-items: center; height: 100%;">
                                <img src="<?= $baseUrl ?>assets/img/notif.png" alt="Notifications" style="width: 20px; height: 20px; object-fit: contain;">
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
        <?php
        return ob_get_clean();
    }
}
