<?php
/**
 * HomeController
 * Handles the homepage and contact page
 */
class HomeController extends Controller {

    /**
     * Homepage - Display slideshow and recent news
     * 
     * Note: This controller loads multiple models (NewsModel, ProjectModel, 
     * PublicationModel, PartnerModel). While this creates coupling and may be 
     * considered a "God Controller", it is acceptable for a homepage/dashboard 
     * that serves as an aggregation point. Future refactoring could move data 
     * aggregation to a dedicated service layer if complexity grows.
     */
    public function index() {
        // Load language
        $lang = $this->loadLang('fr');
        
        // Load Models
        $newsModel = $this->model('NewsModel');
        $projectModel = $this->model('ProjectModel');
        $publicationModel = $this->model('PublicationModel');
        $partnerModel = $this->model('PartnerModel');
        
        // Get data for homepage
        $recentNews = $newsModel->getRecentNews(5); // For slideshow
        $recentProjects = $projectModel->getRecentProjects(2);
        $recentPublications = $publicationModel->getRecentPublications(3);
        $recentPartners = $partnerModel->getRecentPartners(2);
        $allPartners = $partnerModel->getAllPartnersForDisplay(); // For logo grid

        $eventsPerPage = 3;
        $eventsPage = 1;
        // Fetch all upcoming events at once for client-side pagination
        $allUpcomingEvents = $newsModel->getAllUpcomingEvents();
        $totalUpcoming = count($allUpcomingEvents);
        $eventsTotalPages = (int)ceil($totalUpcoming / $eventsPerPage);
        // Get first page for initial display
        $upcomingEvents = array_slice($allUpcomingEvents, 0, $eventsPerPage);
        
        // Prepare data (ensure arrays even if empty)
        $data = [
            'recentNews' => $recentNews ?: [],
            'recentProjects' => $recentProjects ?: [],
            'recentPublications' => $recentPublications ?: [],
            'recentPartners' => $recentPartners ?: [],
            'allPartners' => $allPartners ?: [],
            'upcomingEvents' => $upcomingEvents ?: [],
            'allUpcomingEvents' => $allUpcomingEvents ?: [], // Pass all events for JS
            'eventsPage' => $eventsPage,
            'eventsTotalPages' => $eventsTotalPages,
            'eventsPerPage' => $eventsPerPage
        ];
        
        // Load Home View
        $this->view('Home', $data, $lang);
    }

    /**
     * AJAX: Get upcoming events page (JSON)
     */
    public function upcomingEvents() {
        $newsModel = $this->model('NewsModel');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 3;
        $page = max(1, $page);
        $perPage = max(1, min(12, $perPage));

        $events = $newsModel->getUpcomingEventsPaginated($page, $perPage);
        $total = $newsModel->countUpcomingEvents();
        $totalPages = (int)ceil($total / $perPage);

        $this->json([
            'success' => true,
            'data' => $events ?: [],
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ]
        ]);
    }

    /**
     * Contact page
     */
    public function contact() {
        $lang = $this->loadLang('fr');
        
        $data = [
            'pageTitle' => 'Contact'
        ];
        
        $this->view('Contact', $data, $lang);
    }
}
