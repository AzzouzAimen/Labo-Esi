<?php
/**
 * HomeController
 * Handles the homepage and contact page
 */
class HomeController extends Controller
{

    /**
     * Homepage - Display slideshow and recent news
     * 
     * Now uses dynamic layout configuration from database
     */
    public function index()
    {
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
        $eventsTotalPages = (int) ceil($totalUpcoming / $eventsPerPage);
        // Get first page for initial display
        $upcomingEvents = array_slice($allUpcomingEvents, 0, $eventsPerPage);

        // Load dynamic layout configuration from database
        $components = $this->loadDynamicComponents('home', [
            'recentNews' => $recentNews ?: [],
            'recentProjects' => $recentProjects ?: [],
            'recentPublications' => $recentPublications ?: [],
            'recentPartners' => $recentPartners ?: [],
            'allPartners' => $allPartners ?: [],
            'upcomingEvents' => $upcomingEvents ?: [],
            'allUpcomingEvents' => $allUpcomingEvents ?: [],
            'eventsPage' => $eventsPage,
            'eventsTotalPages' => $eventsTotalPages,
            'eventsPerPage' => $eventsPerPage,
            'lang' => $lang,
            'baseUrl' => BASE_URL
        ]);

        // Prepare data
        $data = [
            'components' => $components,
            // Keep legacy data for backward compatibility if needed
            'recentNews' => $recentNews ?: [],
            'recentProjects' => $recentProjects ?: [],
            'recentPublications' => $recentPublications ?: [],
            'recentPartners' => $recentPartners ?: [],
            'allPartners' => $allPartners ?: [],
            'upcomingEvents' => $upcomingEvents ?: [],
            'allUpcomingEvents' => $allUpcomingEvents ?: [],
            'eventsPage' => $eventsPage,
            'eventsTotalPages' => $eventsTotalPages,
            'eventsPerPage' => $eventsPerPage
        ];

        // Load Home View
        $this->view('Home', $data, $lang);
    }

    /**
     * Load dynamic components from database configuration
     * 
     * @param string $pageName The page name to load layout for
     * @param array $pageData All available data for component props
     * @return array Array of instantiated component objects
     */
    private function loadDynamicComponents($pageName, $pageData)
    {
        $components = [];
        
        try {
            // Get layout configuration from database
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT component_class, props_json, order_index
                FROM layout_settings
                WHERE page_name = :pageName AND is_visible = 1
                ORDER BY order_index ASC
            ");
            $stmt->execute([':pageName' => $pageName]);
            $layoutConfig = $stmt->fetchAll();
            
            // Instantiate each component with its configured props
            foreach ($layoutConfig as $config) {
                $componentClass = $config['component_class'];
                
                // Check if component class exists
                if (!class_exists($componentClass)) {
                    error_log("Component class not found: {$componentClass}");
                    continue;
                }
                
                // Merge default props with JSON config if provided
                $props = $pageData;
                if (!empty($config['props_json'])) {
                    $jsonProps = json_decode($config['props_json'], true);
                    if ($jsonProps) {
                        $props = array_merge($props, $jsonProps);
                    }
                }
                
                // Instantiate and store component
                $components[] = new $componentClass($props);
            }
        } catch (Exception $e) {
            error_log("Error loading dynamic components: " . $e->getMessage());
        }
        
        return $components;
    }

    /**
     * AJAX: Get upcoming events page (JSON)
     */
    public function upcomingEvents()
    {
        $newsModel = $this->model('NewsModel');

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 3;
        $page = max(1, $page);
        $perPage = max(1, min(12, $perPage));

        $events = $newsModel->getUpcomingEventsPaginated($page, $perPage);
        $total = $newsModel->countUpcomingEvents();
        $totalPages = (int) ceil($total / $perPage); // Calculate total pages

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
    public function contact()
    {
        $lang = $this->loadLang('fr');

        $data = [
            'pageTitle' => 'Contact'
        ];

        $this->view('Contact', $data, $lang);
    }

    /**
     * Handle contact form submission
     */
    public function sendContact()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            $errors = [];

            if (empty($name))
                $errors[] = "Le nom est requis.";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                $errors[] = "L'email n'est pas valide.";
            if (empty($subject))
                $errors[] = "L'objet est requis.";
            if (empty($message))
                $errors[] = "Le message est requis.";

            $lang = $this->loadLang('fr');

            if (empty($errors)) {
                // TODO: Implement actual email sending logic here
                // For now, we simulate success

                $data = [
                    'pageTitle' => 'Contact',
                    'success' => "Votre message a été envoyé avec succès. Nous vous contacterons bientôt."
                ];

                $this->view('Contact', $data, $lang);
                return;
            } else {
                $data = [
                    'pageTitle' => 'Contact',
                    'error' => implode('<br>', $errors),
                    'formData' => $_POST
                ];

                $this->view('Contact', $data, $lang);
                return;
            }
        }

        // If not POST, redirect to contact page
        // Assuming BASE_URL is defined, otherwise use relative path
        $redirectUrl = defined('BASE_URL') ? BASE_URL . 'index.php?controller=Home&action=contact' : 'index.php?controller=Home&action=contact';
        header('Location: ' . $redirectUrl);
    }
}
