<?php
/**
 * PublicationController
 * Handles publication listing and details
 */
class PublicationController extends Controller {

    /**
     * Publications listing page
     */
    public function index() {
        $lang = $this->loadLang('fr');

        $pubModel = $this->model('PublicationModel');

        $teamId = $_GET['team_id'] ?? null;

        $filters = [
            'year' => $_GET['year'] ?? 'all',
            'type' => $_GET['type'] ?? 'all',
            'domain' => $_GET['domain'] ?? 'all',
            'author' => $_GET['author'] ?? 'all',
            'team' => $teamId ? (int)$teamId : ($_GET['team'] ?? 'all'),
            'q' => trim($_GET['q'] ?? '')
        ];

        $sort = $_GET['sort'] ?? 'date_desc';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 6;

        $publications = $pubModel->searchPublications($filters, $page, $perPage, $sort);
        $total = $pubModel->countPublications($filters);
        $totalPages = (int)ceil($total / $perPage);

        $data = [
            'pageTitle' => $lang['publications_title'],
            'publications' => $publications ?: [],
            'years' => $pubModel->getYears(),
            'types' => $pubModel->getTypes(),
            'domains' => $pubModel->getDomains(),
            'authors' => $pubModel->getAuthors(),
            'filters' => $filters,
            'sort' => $sort,
            'page' => max(1, $page),
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'total' => $total
        ];

        $this->view('Publication', $data, $lang);
    }

    /**
     * AJAX filter endpoint for publications
     */
    public function filter() {
        $pubModel = $this->model('PublicationModel');

        $filters = [
            'year' => $_GET['year'] ?? 'all',
            'type' => $_GET['type'] ?? 'all',
            'domain' => $_GET['domain'] ?? 'all',
            'author' => $_GET['author'] ?? 'all',
            'team' => $_GET['team'] ?? 'all',
            'q' => trim($_GET['q'] ?? '')
        ];

        $sort = $_GET['sort'] ?? 'date_desc';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 6;
        $page = max(1, $page);
        $perPage = max(1, min(24, $perPage));

        $publications = $pubModel->searchPublications($filters, $page, $perPage, $sort);
        $total = $pubModel->countPublications($filters);
        $totalPages = (int)ceil($total / $perPage);

        $this->json([
            'success' => true,
            'data' => $publications ?: [],
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ]
        ]);
    }
}
