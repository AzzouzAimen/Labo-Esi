<?php
/**
 * EventController
 * Minimal event detail page for links from the homepage
 */
class EventController extends Controller {
    public function detail() {
        $lang = $this->loadLang('fr');

        $eventId = $_GET['id'] ?? null;
        if (!$eventId) {
            $this->redirect('Home', 'index');
            return;
        }

        $newsModel = $this->model('NewsModel');
        $event = $newsModel->getEventById((int)$eventId);

        if (!$event) {
            $this->redirect('Home', 'index');
            return;
        }

        $data = [
            'event' => $event
        ];

        $this->view('EventDetail', $data, $lang);
    }
}
