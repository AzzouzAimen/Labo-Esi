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
        $upcomingEvents = $newsModel->getUpcomingEvents(3);
        
        // Prepare data (ensure arrays even if empty)
        $data = [
            'recentNews' => $recentNews ?: [],
            'upcomingEvents' => $upcomingEvents ?: []
        ];
        
        // Load Home View
        $this->view('Home', $data, $lang);
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
