<?php
/**
 * HomeController
 * Handles the homepage and contact page
 */
class HomeController extends Controller {

    /**
     * Homepage - Display slideshow and recent news
     */
    public function index() {
        // Load language
        $lang = $this->loadLang('fr');
        
        // Load News Model
        $newsModel = $this->model('NewsModel');
        
        // Get data for homepage
        $recentNews = $newsModel->getRecentNews(5); // For slideshow

        $eventsPerPage = 3;
        $eventsPage = 1;
        $upcomingEvents = $newsModel->getUpcomingEventsPaginated($eventsPage, $eventsPerPage);
        $totalUpcoming = $newsModel->countUpcomingEvents();
        $eventsTotalPages = (int)ceil($totalUpcoming / $eventsPerPage);
        
        // Prepare data (ensure arrays even if empty)
        $data = [
            'recentNews' => $recentNews ?: [],
            'upcomingEvents' => $upcomingEvents ?: [],
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
