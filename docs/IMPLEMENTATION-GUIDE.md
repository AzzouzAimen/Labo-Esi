# Implementation Guide

Complete guide for understanding the new feature implementations.

---

## Architecture Overview

### MVC Pattern Flow
```
User Request
    ↓
Router (core/Router.php)
    ↓ Parses ?controller=X&action=Y
    ↓
Controller (app/Controllers/XController.php)
    ├── Load Model: $this->model('ModelName')
    ├── Call Model Methods: $modelResult = $model->getData()
    ├── Load View: $this->view('ViewName', $data, $lang)
    │   └── View Class (app/Views/Classes/ViewNameView.php)
    │       └── Renders Template (app/Views/Templates/xxx.phtml)
    │           └── Wrapped in LayoutView
    ├── OR Return JSON: $this->json($data)
    └── OR Redirect: $this->redirect('url')
```

---

## File Structure Reference

```
app/
├── Controllers/
│   ├── HomeController.php          [MODIFIED] + upcomingEvents action
│   ├── ProjectController.php        [MODIFIED] + edit/update/getSupervisors
│   ├── PublicationController.php    [MODIFIED] filter() + search logic
│   ├── EquipmentController.php      [MODIFIED] reserve() + list with state
│   ├── DashboardController.php      [MODIFIED] + reservations data
│   ├── OfferController.php          [NEW] Simple offer page
│   ├── EventController.php          [NEW] Event detail page
│   └── [Other controllers unchanged]
│
├── Models/
│   ├── NewsModel.php                [MODIFIED] + getUpcomingEventsPaginated
│   ├── ProjectModel.php             [MODIFIED] + getSupervisors, updateProjectByManager
│   ├── PublicationModel.php         [NEW] searchPublications, countPublications, filters
│   ├── EquipmentModel.php           [NEW] getAllEquipment, createReservation, state logic
│   ├── TeamModel.php                [MODIFIED] getUserProjects includes responsable_id
│   └── [Other models unchanged]
│
└── Views/
    ├── Classes/
    │   ├── ProjectEditView.php      [NEW]
    │   ├── OrgChartView.php         [NEW]
    │   ├── ProfileEditView.php      [NEW]
    │   ├── OfferView.php            [NEW]
    │   ├── EventDetailView.php      [NEW]
    │   └── [Other views unchanged]
    │
    └── Templates/
        ├── home.phtml               [MODIFIED] + pagination controls, orgchart button
        ├── team_list.phtml          [MODIFIED] + filter bar, pub link per team
        ├── project_list.phtml       [MODIFIED] + funding/members display
        ├── dashboard.phtml          [MODIFIED] + reservations section + edit buttons
        ├── publication_list.phtml   [NEW] Full filter/search/pagination UI
        ├── equipment_list.phtml     [NEW] Full list with reservation form
        ├── project_edit.phtml       [NEW] Manager edit form
        ├── org_chart.phtml          [NEW] Organizational chart by role
        ├── profile_edit.phtml       [NEW] Profile update form
        ├── offers.phtml             [NEW] Offers landing page
        ├── event_detail.phtml       [NEW] Event details
        ├── layout.phtml             [MODIFIED] + Offers nav menu
        └── [Other templates unchanged]

core/
└── [No changes needed]

public/
├── index.php                         [No changes needed, routing works as-is]
└── assets/
    └── js/
        └── script.js                [MODIFIED] + pagination, filters, AJAX handlers

lang/
└── fr.php                           [MODIFIED] + new labels

docs/
├── FEATURES-IMPLEMENTATION.md       [NEW] This complete feature guide
├── API-REFERENCE.md                 [NEW] API endpoints reference
└── IMPLEMENTATION-GUIDE.md          [NEW] This file
```

---

## Key Implementation Patterns

### 1. Model Methods Pattern

**Single Record Query**
```php
public function getEventById($eventId) {
    $stmt = $this->db->prepare("
        SELECT * FROM events 
        WHERE id_event = :id
    ");
    $stmt->execute([':id' => $eventId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
```

**Paginated Results**
```php
public function getUpcomingEventsPaginated($page = 1, $perPage = 3) {
    $offset = ($page - 1) * $perPage;
    $stmt = $this->db->prepare("
        SELECT * FROM events 
        WHERE date_event >= NOW()
        ORDER BY date_event ASC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

**Aggregated Results (GROUP_CONCAT)**
```php
public function getAllEquipment($filters = []) {
    $query = "
        SELECT 
            e.*,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM maintenances m 
                    WHERE m.id_equip = e.id_equip 
                    AND NOW() BETWEEN m.date_debut AND m.date_fin
                ) THEN 'maintenance'
                WHEN EXISTS (
                    SELECT 1 FROM reservations r 
                    WHERE r.id_equip = e.id_equip 
                    AND NOW() BETWEEN r.date_debut AND r.date_fin
                ) THEN 'réservé'
                ELSE 'libre'
            END as etat_actuel
        FROM equipment e
        WHERE 1=1
    ";
    // Add filter conditions...
    return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}
```

### 2. Controller Action Pattern

**HTML Response**
```php
public function index() {
    $projectModel = $this->model('Project');
    $supervisors = $projectModel->getSupervisors();
    $projects = $projectModel->getAllProjects();
    
    $lang = $this->loadLanguage('fr');
    $this->view('Project', [
        'projects' => $projects,
        'supervisors' => $supervisors
    ], $lang);
}
```

**AJAX JSON Response**
```php
public function filter() {
    $pubModel = $this->model('Publication');
    $filters = [
        'year' => $_GET['year'] ?? null,
        'type' => $_GET['type'] ?? null,
        'q' => $_GET['q'] ?? null,
    ];
    
    $page = (int)($_GET['page'] ?? 1);
    $perPage = (int)($_GET['perPage'] ?? 6);
    
    $publications = $pubModel->searchPublications($filters, $page, $perPage);
    $total = $pubModel->countPublications($filters);
    
    $this->json([
        'success' => true,
        'data' => $publications,
        'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]
    ]);
}
```

**Form POST Response**
```php
public function reserve() {
    $equipModel = $this->model('Equipment');
    $result = $equipModel->createReservation(
        $_SESSION['user_id'],
        $_POST['equip_id'],
        $_POST['date_debut'],
        $_POST['date_fin'],
        $_POST['motif'] ?? null
    );
    
    if ($result['success']) {
        $this->redirect('?controller=Equipment&action=index&success=1');
    } else {
        $this->redirect('?controller=Equipment&action=index&error=' . urlencode($result['error']));
    }
}
```

### 3. View Rendering Pattern

**Simple View Class**
```php
// app/Views/Classes/OfferView.php
<?php
namespace App\Views\Classes;

class OfferView extends View {
    public function render($data, $lang) {
        ob_start();
        $this->template('offers', $data, $lang);
        $content = ob_get_clean();
        
        return (new LayoutView())->render([
            'content' => $content,
            'title' => $lang['offers_title'] ?? 'Offres'
        ], $lang);
    }
}
```

**View with Data**
```php
// app/Views/Classes/PublicationView.php
<?php
namespace App\Views\Classes;

class PublicationView extends View {
    public function render($data, $lang) {
        ob_start();
        $this->template('publication_list', [
            'publications' => $data['publications'] ?? [],
            'years' => $data['years'] ?? [],
            'authors' => $data['authors'] ?? [],
            'types' => $data['types'] ?? [],
            'domains' => $data['domains'] ?? [],
            'totalPages' => $data['totalPages'] ?? 1
        ], $lang);
        $content = ob_get_clean();
        
        return (new LayoutView())->render([
            'content' => $content,
            'title' => $lang['pub_title'] ?? 'Publications'
        ], $lang);
    }
}
```

### 4. Template Pattern (phtml)

**Basic Template Structure**
```phtml
<div class="container">
    <h1><?php echo htmlspecialchars($lang['pub_title']); ?></h1>
    
    <!-- Filter bar -->
    <div class="filters">
        <select id="pub-filter-year">
            <option value="">Toutes les années</option>
            <?php foreach ($years as $year): ?>
                <option value="<?php echo htmlspecialchars($year); ?>">
                    <?php echo htmlspecialchars($year); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- Results container (AJAX target) -->
    <div id="publications-grid" data-page="1" data-total-pages="<?php echo $totalPages; ?>">
        <?php foreach ($publications as $pub): ?>
            <div class="publication-card">
                <h3><?php echo htmlspecialchars($pub['titre']); ?></h3>
                <!-- ... -->
            </div>
        <?php endforeach; ?>
    </div>
</div>
```

### 5. AJAX Handler Pattern

**jQuery AJAX with Debounce**
```javascript
// Debounce utility
function debounce(fn, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
}

// Collect filter values
function collectPubFilters() {
    return {
        year: document.getElementById('pub-filter-year').value,
        author: document.getElementById('pub-filter-author').value,
        q: document.getElementById('pub-search').value,
        sort: document.getElementById('pub-sort').value
    };
}

// Load page via AJAX
function loadPublicationsPage(targetPage) {
    const filters = collectPubFilters();
    const params = new URLSearchParams({
        ...filters,
        page: targetPage,
        perPage: 6
    });
    
    $.ajax({
        url: 'index.php?controller=Publication&action=filter',
        type: 'GET',
        data: params.toString(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderPublications(response.data);
                updatePubPagerState(response.pagination.page, response.pagination.totalPages);
            }
        }
    });
}

// Render results
function renderPublications(pubs) {
    const container = document.getElementById('publications-grid');
    if (!pubs.length) {
        container.innerHTML = '<p>Aucune publication trouvée</p>';
        return;
    }
    
    const html = pubs.map(pub => `
        <div class="publication-card">
            <h3>${escapeHtml(pub.titre)}</h3>
            <p>${escapeHtml(pub.auteurs)}</p>
            <a href="${escapeHtml(pub.lien_pdf)}">PDF</a>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

// Event binding
document.getElementById('pub-search').addEventListener('input', 
    debounce(() => loadPublicationsPage(1), 300)
);
document.getElementById('pub-filter-year').addEventListener('change', 
    () => loadPublicationsPage(1)
);
document.getElementById('pub-prev').addEventListener('click', 
    () => loadPublicationsPage(currentPubPage - 1)
);
```

---

## Database Integration Details

### Equipment State Computation

The equipment state is **computed dynamically**, not stored:

```sql
-- Real-time state logic
IF equipment has overlapping maintenance record NOW()
    THEN state = 'maintenance'
ELSE IF equipment has overlapping reservation NOW()
    THEN state = 'réservé'  
ELSE
    THEN state = 'libre'
```

This ensures that as time passes, equipment automatically transitions between states.

### Publication Filtering Logic

Publication filtering uses complex JOINs:

```sql
publications
  LEFT JOIN projects p ON publications.id_project = p.id_project
  LEFT JOIN publication_authors pa ON publications.id_pub = pa.id_pub
  LEFT JOIN users u ON pa.id_user = u.id_user
  LEFT JOIN team_members tm ON u.id_user = tm.id_user
```

**Why LEFT JOINs?**
- Publications don't require projects (orphaned publications OK)
- Authors are aggregated (multiple per publication)
- Team filtering uses EXISTS subquery to check team membership

---

## Common Tasks

### Add New Filter to Publications

1. **Database** - Ensure column exists (e.g., `type`, `domaine`)
2. **Model** - Add to `searchPublications()` WHERE clause:
   ```php
   if (!empty($filters['type'])) {
       $query .= " AND p.type = :type";
       $params[':type'] = $filters['type'];
   }
   ```
3. **Controller** - Extract from GET params:
   ```php
   $filters = [
       'type' => $_GET['type'] ?? null,
       // ... other filters
   ];
   ```
4. **Template** - Add dropdown:
   ```phtml
   <select id="pub-filter-type">
       <?php foreach ($types as $type): ?>
           <option value="<?php echo htmlspecialchars($type); ?>">
               <?php echo htmlspecialchars($type); ?>
           </option>
       <?php endforeach; ?>
   </select>
   ```
5. **JavaScript** - Add change event:
   ```javascript
   document.getElementById('pub-filter-type').addEventListener('change', 
       () => loadPublicationsPage(1)
   );
   ```

### Extend Equipment State Logic

Edit `EquipmentModel::getAllEquipment()`:

```php
CASE 
    WHEN EXISTS (SELECT 1 FROM maintenances ...) THEN 'maintenance'
    WHEN EXISTS (SELECT 1 FROM reservations ...) THEN 'réservé'
    WHEN e.etat = 'broken' THEN 'defectueux'  // <- New condition
    ELSE 'libre'
END as etat_actuel
```

### Add New Team Filter

1. **JavaScript** - Add handler in `applyTeamFilters()`:
   ```javascript
   const specialization = document.getElementById('team-filter-spec').value;
   if (specialization) {
       members = members.filter(m => m.dataset.specialization === specialization);
   }
   ```
2. **Template** - Add dropdown:
   ```phtml
   <select id="team-filter-spec">
       <option value="">Toutes spécialisations</option>
       <option value="IA">IA</option>
       <option value="Cloud">Cloud</option>
   </select>
   ```
3. **Data Attributes** - Add to template:
   ```phtml
   <div class="member-card" data-specialization="IA">
   ```

---

## Testing the Implementations

### Unit Test Example: Publication Search

```php
// tests/PublicationSearchTest.php
$pubModel = new PublicationModel();

// Test 1: Get all publications
$all = $pubModel->searchPublications([], 1, 10);
assert(count($all) > 0, "Should return publications");

// Test 2: Filter by year
$2024 = $pubModel->searchPublications(['year' => 2024], 1, 10);
foreach ($2024 as $pub) {
    assert($pub['date_publication'] >= '2024-01-01', "Year filter failed");
}

// Test 3: Search by text
$results = $pubModel->searchPublications(['q' => 'machine'], 1, 10);
foreach ($results as $pub) {
    assert(
        stripos($pub['titre'], 'machine') !== false ||
        stripos($pub['resume'], 'machine') !== false,
        "Text search failed"
    );
}
```

### Integration Test Example: Equipment Reservation

```php
// Reserve equipment
$equipModel = new EquipmentModel();
$result = $equipModel->createReservation(
    userId: 1,
    equipId: 5,
    startTime: '2025-01-15 10:00:00',
    endTime: '2025-01-15 12:00:00',
    reason: 'Testing'
);

assert($result['success'] === true, "Reservation creation failed");

// Verify state changed
$equip = $equipModel->getEquipmentById(5);
assert($equip['etat_actuel'] === 'réservé', "State should be reserved");

// Try overlapping reservation
$overlap = $equipModel->createReservation(
    userId: 2,
    equipId: 5,
    startTime: '2025-01-15 11:00:00',
    endTime: '2025-01-15 13:00:00',
    reason: 'Another test'
);

assert($overlap['success'] === false, "Should reject overlapping reservation");
assert(stripos($overlap['error'], 'chevauche') !== false, "Should mention overlap");
```

---

## Troubleshooting

### Issue: Publications not showing

**Checks**:
1. Database has data: `SELECT COUNT(*) FROM publications;`
2. Model query runs: Test `PublicationModel::getAllPublications()`
3. Template loops: Check `if ($publications)` condition
4. Browser: Clear cache (Ctrl+F5)

### Issue: Equipment state always "libre"

**Checks**:
1. Overlap logic: Test `SELECT * FROM reservations WHERE id_equip = 5 AND NOW() BETWEEN date_debut AND date_fin;`
2. Maintenance: Check if maintenance records exist
3. DateTime format: Ensure `date_debut`/`date_fin` are valid DATETIME format
4. Timezone: Confirm server and MySQL use same timezone

### Issue: AJAX filter returns empty

**Checks**:
1. Console errors: Check browser console (F12)
2. Network: Check response body (F12 → Network → click request)
3. Parameters: Verify GET params are being passed
4. SQL: Test query directly in MySQL with same params
5. Escape: Ensure special chars are URL-encoded

### Issue: Team filters not working

**Checks**:
1. Data attributes: Verify `data-team-id`, `data-grade` exist in HTML
2. JavaScript errors: Check console
3. Selector: Verify IDs match in HTML and JS (`#team-filter-team`, etc.)
4. Event binding: Confirm change event fires (add `console.log`)

---

## Performance Optimization Tips

1. **Index columns frequently filtered**:
   ```sql
   ALTER TABLE publications ADD INDEX idx_year (date_publication);
   ALTER TABLE publications ADD INDEX idx_type (type);
   ALTER TABLE equipment ADD INDEX idx_etat (etat);
   ```

2. **Cache filter dropdowns** (1 hour):
   ```php
   $years = apcu_fetch('pub_years');
   if (!$years) {
       $years = $pubModel->getYears();
       apcu_store('pub_years', $years, 3600);
   }
   ```

3. **Pagination**: Default to 6-10 items per page (not 100)

4. **Debounce search**: 300ms delay prevents excessive AJAX calls

5. **GROUP_CONCAT**: Usually OK for <100 items per group; consider LIMIT if data grows

---

## Version Control Notes

All files added/modified in single session:
- Commit message: "Implement 8 features: events pagination, publications search, equipment reservations, etc."
- No database migrations needed (schema already supports all features)
- No breaking changes to existing code

---

## Next Steps

If extending further:

1. **Real Offers Data** - Replace offers.phtml placeholders with database-backed listings
2. **Notifications** - Email users on reservation confirmation/equipment-ready
3. **Export** - Add PDF export for publications list
4. **Analytics** - Dashboard showing most reserved equipment, most-cited authors, etc.
5. **Admin Interface** - Create equipment/maintenance/event management pages
