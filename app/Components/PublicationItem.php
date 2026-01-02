<?php
/**
 * PublicationItem Component
 * 
 * Displays a single publication in horizontal list format with title, author,
 * year, abstract excerpt, DOI, and PDF download link.
 * 
 * Required props:
 * - publication: array with publication data (titre, auteur, type, date_publication, resume, doi, lien_pdf, domaine)
 * - lang: array with translations
 * 
 * Optional props:
 * - truncateAbstract: int - Maximum characters for abstract (default: 250)
 */
class PublicationItem extends Component {
    public function render(): string {
        $pub = $this->props['publication'] ?? [];
        $lang = $this->props['lang'] ?? [];
        $truncateLength = $this->props['truncateAbstract'] ?? 250;

        if (empty($pub)) {
            return '';
        }

        $titre = htmlspecialchars($pub['titre'] ?? '', ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars($pub['type'] ?? '', ENT_QUOTES, 'UTF-8');
        $auteur = htmlspecialchars($pub['auteur'] ?? '', ENT_QUOTES, 'UTF-8');
        $domaine = htmlspecialchars($pub['domaine'] ?? '', ENT_QUOTES, 'UTF-8');
        $doi = htmlspecialchars($pub['doi'] ?? '', ENT_QUOTES, 'UTF-8');
        $resume = htmlspecialchars($pub['resume'] ?? '', ENT_QUOTES, 'UTF-8');
        $lienPdf = htmlspecialchars($pub['lien_pdf'] ?? '', ENT_QUOTES, 'UTF-8');
        
        // Format date
        $dateFormatted = 'N/A';
        if (!empty($pub['date_publication'])) {
            $dateFormatted = date('d/m/Y', strtotime($pub['date_publication']));
        }

        // Truncate abstract
        $resumeShort = '';
        if (!empty($resume)) {
            $resumeShort = substr($resume, 0, $truncateLength);
            if (strlen($resume) > $truncateLength) {
                $resumeShort .= '...';
            }
        }

        ob_start();
        ?>
        <div class="document-item">
            <div class="doc-header">
                <h3 class="doc-title"><?= $titre ?></h3>
                <span class="doc-type-badge"><?= $type ?></span>
            </div>

            <div class="doc-meta-row">
                <span>
                    <strong><?= htmlspecialchars($lang['pub_date'] ?? 'Date', ENT_QUOTES, 'UTF-8') ?>:</strong> 
                    <?= $dateFormatted ?>
                </span>
                <?php if (!empty($domaine)): ?>
                    <span>
                        <strong><?= htmlspecialchars($lang['project_domain'] ?? 'Domaine', ENT_QUOTES, 'UTF-8') ?>:</strong> 
                        <?= $domaine ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="doc-authors">
                <strong><?= htmlspecialchars($lang['pub_author'] ?? 'Auteur', ENT_QUOTES, 'UTF-8') ?>:</strong> 
                <?php if (!empty($auteur)): ?>
                    <?= $auteur ?>
                <?php else: ?>
                    <span class="empty-field">Aucun auteur</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($resumeShort)): ?>
                <div class="doc-abstract">
                    <strong><?= htmlspecialchars($lang['pub_abstract'] ?? 'Résumé', ENT_QUOTES, 'UTF-8') ?>:</strong><br>
                    <?= $resumeShort ?>
                </div>
            <?php else: ?>
                <div class="doc-abstract">
                    <strong><?= htmlspecialchars($lang['pub_abstract'] ?? 'Résumé', ENT_QUOTES, 'UTF-8') ?>:</strong><br>
                    <span class="empty-field">Aucun résumé disponible.</span>
                </div>
            <?php endif; ?>

            <div class="doc-footer">
                <span class="doc-doi">
                    DOI: <?php if (!empty($doi)): ?>
                        <?= $doi ?>
                    <?php else: ?>
                        <span class="empty-field">Non disponible</span>
                    <?php endif; ?>
                </span>
                
                <?php if (!empty($lienPdf)): ?>
                    <a href="<?= $lienPdf ?>" target="_blank" class="btn-download">
                        <?= htmlspecialchars($lang['pub_download'] ?? 'Télécharger', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php else: ?>
                    <span class="btn-download disabled">
                        <?= htmlspecialchars($lang['pub_download'] ?? 'Télécharger', ENT_QUOTES, 'UTF-8') ?> (Indisponible)
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
