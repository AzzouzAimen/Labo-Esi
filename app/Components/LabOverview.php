<?php
/**
 * LabOverview Component
 * 
 * Displays an overview/description of the laboratory
 */
class LabOverview extends Component {
    /**
     * Render the lab overview HTML
     * 
     * Expected props:
     * - lang: Array of language strings
     * - baseUrl: String base URL
     * 
     * @return string The rendered HTML
     */
    public function render(): string {
        $lang = $this->props['lang'] ?? [];
        $baseUrl = $this->props['baseUrl'] ?? BASE_URL;
        
        ob_start();
        ?>
        <!-- Lab Overview Section -->
        <section class="lab-overview mt-4">
            <h2 class="section-title"><?= $lang['lab_overview'] ?? 'Aperçu du Laboratoire' ?></h2>
            <div style="background: var(--bg-light); padding: 2rem;">
                <p style="line-height: 1.8; font-size: 1.1rem;">
                    <?= $lang['lab_description_intro'] ?? 'Description du laboratoire' ?>
                </p>
                <ul style="line-height: 1.8; font-size: 1.05rem; margin: 1.5rem 0; padding-left: 2rem;">
                    <li><?= $lang['lab_description_list_1'] ?? 'Point 1' ?></li>
                    <li><?= $lang['lab_description_list_2'] ?? 'Point 2' ?></li>
                    <li><?= $lang['lab_description_list_3'] ?? 'Point 3' ?></li>
                    <li><?= $lang['lab_description_list_4'] ?? 'Point 4' ?></li>
                </ul>
                <p style="line-height: 1.8; font-size: 1.1rem;">
                    <?= $lang['lab_description_outro_1'] ?? 'Conclusion 1' ?>
                </p>
                <p style="line-height: 1.8; font-size: 1.1rem; margin-top: 1rem;">
                    <?= $lang['lab_description_outro_2'] ?? 'Conclusion 2' ?>
                </p>

                <div style="margin-top: 1.5rem;">
                    <a href="<?= $baseUrl ?>index.php?controller=Team&action=presentation#organigramme-section" class="btn btn-secondary">
                        <?= $lang['view_organigram'] ?? 'Voir l\'organigramme' ?>
                    </a>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
