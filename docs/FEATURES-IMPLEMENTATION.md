# New Features Documentation

This document describes all the new features added to complete the project requirements.

## Overview

The following sections have been implemented:

1. **Homepage Events Pagination** - Dynamic loading of upcoming events with Prev/Next buttons
2. **Homepage Organigramme Link** - Direct CTA button to view organizational chart
3. **Teams Page Enhancements** - Filter/sort members + team publications links
4. **Projects Page Enhancements** - Funding type + members count + supervisor filter
5. **Dashboard Enhancements** - Equipment reservations display + project edit actions
6. **Publications Page** - Full implementation with filtering, search, and sorting
7. **Equipment Page** - Equipment listing with reservations and usage statistics
8. **Offers & Opportunities** - New section for internships, theses, scholarships, and collaborations

---

## 1. Homepage Events Pagination

### Files Modified
- `app/Controllers/HomeController.php`
- `app/Models/NewsModel.php`
- `app/Views/Templates/home.phtml`
- `public/assets/js/script.js`

### Features
- Initial page shows 3 upcoming events (server-rendered)
- Prev/Next buttons for pagination
- AJAX loading with page/perPage parameters
- Page counter (Page X / Y)

### New Endpoints
```
GET /index.php?controller=Home&action=upcomingEvents&page=2&perPage=3
```

### Response Format
```json
{
  "success": true,
  "data": [
    {
      "id_event": 1,
      "titre": "...",
      "description": "...",
      "date_event": "2025-06-15 09:00:00",
      "type": "conference",
      "image_url": "...",
      "lieu": "..."
    }
  ],
  "pagination": {
    "page": 2,
    "perPage": 3,
    "total": 10,
    "totalPages": 4
  }
}
```

### JavaScript Handlers
- `updateEventsPagerState(page, totalPages)` - Update button states
- `renderUpcomingEvents(events)` - Render event cards
- `loadUpcomingEventsPage(targetPage)` - AJAX call wrapper
- Buttons: `#events-prev`, `#events-next`

---

## 2. Homepage Organigramme Link

### Files Modified
- `app/Views/Templates/home.phtml`

### Features
- Added "Voir l'organigramme" button in Lab Overview section
- Links to `/index.php?controller=Team&action=orgChart`
- Styled consistently with other CTAs

---

## 3. Teams Page Enhancements

### Files Modified
- `app/Views/Templates/team_list.phtml`
- `public/assets/js/script.js`

### Features

#### 3.1 Team Publications Link
Each team section now has a button: **"Voir les publications de l'équipe"**
```html
<a href="index.php?controller=Publication&action=index&team_id=X">
  Voir les publications de l'équipe
</a>
```

#### 3.2 Client-Side Filtering & Sorting
- **Team Filter** (`#team-filter-team`) - Show/hide teams
- **Grade Filter** (`#team-filter-grade`) - Filter members by grade
- **Sort Options** (`#team-sort`):
  - Name (A→Z)
  - Grade (A→Z)

### JavaScript Handlers
```javascript
applyTeamFilters()  // Apply filters and sorting
```

### Data Attributes
- `.team-section` has `data-team-id`
- `.member-card` has `data-grade` and `data-name`

---

## 4. Projects Page Enhancements

### Files Modified
- `app/Models/ProjectModel.php`
- `app/Controllers/ProjectController.php`
- `app/Views/Templates/project_list.phtml`
- `public/assets/js/script.js`

### Features

#### 4.1 Enhanced Project Cards
Project cards now display:
- **Funding Type** (e.g., "Interne", "Externe", "ANR")
- **Associated Members Count** (e.g., "3 members")
- Original fields: Title, Domain, Manager, Status

#### 4.2 Supervisor Filter
New select dropdown `#filter-supervisor` populated with supervisors who manage at least one project.

```html
<select id="filter-supervisor">
  <option value="all">Tous</option>
  <option value="3">Mohamed Benali</option>
  <option value="1">Laboratoire Directeur</option>
</select>
```

#### 4.3 Database Queries Enhanced
- `getAllProjects()` now includes `membres_count` and `membres_noms` via subquery
- `filterProjects()` adds optional `$supervisorId` parameter
- New method: `getSupervisors()` - returns all supervisors with projects
- New method: `updateProjectByManager()` - allows managers to edit projects

### New Endpoints

**Filter Projects**
```
GET /index.php?controller=Project&action=filter&domain=IA&status=en%20cours&supervisor=3
```

**Edit Project (manager only)**
```
GET /index.php?controller=Project&action=edit&id=5
```

**Update Project (manager only)**
```
POST /index.php?controller=Project&action=update
Fields: id_project, titre, description, domaine, statut, type_financement, image_url
```

### Response Format (Filter)
```json
{
  "success": true,
  "data": [
    {
      "id_project": 1,
      "titre": "Smart City Algiers",
      "domaine": "IA",
      "statut": "en cours",
      "type_financement": "Interne",
      "membres_count": 3,
      "membres_noms": "Sarah Amrani, Yacine Kader, ...",
      "responsable_nom": "Benali",
      "responsable_prenom": "Mohamed",
      ...
    }
  ]
}
```

---

## 5. Dashboard Enhancements

### Files Modified
- `app/Controllers/DashboardController.php`
- `app/Views/Templates/dashboard.phtml`
- `app/Models/TeamModel.php`
- `app/Models/EquipmentModel.php`

### Features

#### 5.1 Equipment Reservations Section ("Mes Réservations")
Displays user's current and past equipment reservations:
- Equipment name
- Reservation period (start/end date-time)
- Reservation purpose/reason (optional)
- Status badge: "à venir", "en cours", "passée"

#### 5.2 Project Management
For projects where the user is the manager:
- **Edit Button** links to `/index.php?controller=Project&action=edit&id=X`
- Shown only when `project.responsable_id == user_id`
- Allows managers to update title, description, domain, status, funding type, image

### Database Queries
- `TeamModel::getUserProjects()` now includes `responsable_id`
- `EquipmentModel::getUserReservations()` - returns user's reservations with computed status

---

## 6. Publications Page

### Files Modified
- `app/Controllers/PublicationController.php`
- `app/Models/PublicationModel.php` (NEW)
- `app/Views/Templates/publication_list.phtml`
- `public/assets/js/script.js`

### Features

#### 6.1 Publication Cards Display
Each publication shows:
- Title
- Date (dd/mm/yyyy)
- Type (article, thesis, report, poster, communication)
- Domain (if linked to a project)
- Authors (concatenated from publication_authors table)
- DOI
- Abstract (truncated to 220 chars)
- PDF download link (if available)

#### 6.2 Filtering System
All filters can be combined (AJAX reload):
- **Year** - by publication year
- **Author** - filter by author
- **Type** - publication type
- **Domain** - linked project domain
- **Search** (debounced) - search title, abstract, DOI

#### 6.3 Sorting Options
- Date (Recent → Old) **default**
- Date (Old → Recent)
- Title (A → Z)
- Title (Z → A)

#### 6.4 Pagination
- Default 6 items per page
- Prev/Next buttons
- Page counter

#### 6.5 Team Filter (Pre-filtering)
When accessed from team page via `?team_id=X`, automatically filters to show only that team's publications.

### New Endpoints

**List Publications (with filters)**
```
GET /index.php?controller=Publication&action=index&year=2024&author=3&type=article&domain=IA&sort=date_asc&page=1
```

**AJAX Filter**
```
GET /index.php?controller=Publication&action=filter&year=2024&author=3&type=article&domain=IA&sort=date_asc&page=1&perPage=6&q=machine
```

### Response Format (Filter)
```json
{
  "success": true,
  "data": [
    {
      "id_pub": 5,
      "titre": "Deep Learning for Smart Cities",
      "resume": "This paper explores...",
      "date_publication": "2024-05-15",
      "lien_pdf": "uploads/docs/...",
      "doi": "10.1234/example",
      "type": "article",
      "domaine": "IA",
      "auteurs": "Sarah Amrani, Mohamed Benali"
    }
  ],
  "pagination": {
    "page": 1,
    "perPage": 6,
    "total": 45,
    "totalPages": 8
  }
}
```

### Database Queries (PublicationModel)
- `searchPublications()` - complex filtering with GROUP_CONCAT for authors
- `countPublications()` - count matching publications
- `getYears()`, `getTypes()`, `getAuthors()`, `getDomains()` - for dropdowns

---

## 7. Equipment Page

### Files Modified
- `app/Controllers/EquipmentController.php`
- `app/Models/EquipmentModel.php` (NEW)
- `app/Views/Templates/equipment_list.phtml`

### Features

#### 7.1 Equipment Listing
Each equipment card displays:
- Name
- Category (Salle, Serveur, PC, Robot, etc.)
- Reference/ID
- Current state badge:
  - **Libre** (green) - available
  - **Réservé** (yellow) - currently reserved
  - **En maintenance** (blue) - under maintenance
- Description (truncated)

#### 7.2 Computed Equipment State
State is dynamically computed based on:
1. Check if equipment is under maintenance (overlapping maintenance record)
2. Check if equipment has active reservation
3. Default to "libre" if neither applies

#### 7.3 Reservation System
Logged-in users can reserve equipment inline:
- Date/time pickers for start and end
- Optional reason/purpose field
- Overlap detection:
  - Rejects if overlaps with existing reservation
  - Rejects if overlaps with scheduled maintenance
  - Must end after start time

#### 7.4 Usage Statistics
"Statistiques d'utilisation" section shows:
- Top 5 most reserved equipment
- Reservation count per item

#### 7.5 Filtering
- **Search** by name, reference, description
- **Category** filter (Salle, Serveur, etc.)
- **Status** filter (libre, réservé, maintenance)

### New Endpoints

**List Equipment (with filters)**
```
GET /index.php?controller=Equipment&action=index?category=Serveur&status=libre&q=dell
```

**Reserve Equipment**
```
POST /index.php?controller=Equipment&action=reserve
Fields: equip_id, date_debut (datetime-local), date_fin (datetime-local), motif (optional)
```

### Response on Reserve
- Success: redirects back with `?success=1`
- Error: redirects back with `?error=<message>`

### Database Queries (EquipmentModel)
- `getAllEquipment($filters)` - list with computed state
- `createReservation()` - overlap checking + creation
- `getUserReservations($userId)` - user's reservations
- `getUsageStats($limit)` - most reserved equipment

---

## 8. Offers & Opportunities Page

### Files Modified
- `app/Controllers/OfferController.php` (NEW)
- `app/Views/Classes/OfferView.php` (NEW)
- `app/Views/Templates/offers.phtml` (NEW)
- `app/Views/Templates/layout.phtml`
- `lang/fr.php`

### Features
- New navigation menu item: "Offres"
- Placeholder page with card grid showing:
  - **Stages** (Internships)
  - **Thèses** (Doctoral positions)
  - **Bourses** (Scholarships/Grants)
  - **Collaborations** (Industrial/Academic partnerships)

### URL
```
/index.php?controller=Offer&action=index
```

---

## 9. Supporting Pages (Added)

### Event Detail Page
- **Controller**: `EventController` (NEW)
- **Template**: `event_detail.phtml` (NEW)
- **View Class**: `EventDetailView` (NEW)

Used when clicking "Voir les détails" on homepage events.

```
GET /index.php?controller=Event&action=view&id=5
```

### Project Edit Page
- **Controller**: `ProjectController::edit()` & `::update()`
- **Template**: `project_edit.phtml` (NEW)
- **View Class**: `ProjectEditView` (NEW)

Manager-only form to edit project details.

```
GET /index.php?controller=Project&action=edit&id=5
POST /index.php?controller=Project&action=update
```

### Organizational Chart Page
- **Controller**: `TeamController::orgChart()`
- **Template**: `org_chart.phtml` (NEW)
- **View Class**: `OrgChartView` (NEW)

Displays all laboratory members grouped by role.

```
GET /index.php?controller=Team&action=orgChart
```

---

## JavaScript Enhancements

### New Functions Added to `script.js`

#### Events Pagination
```javascript
updateEventsPagerState(page, totalPages)
renderUpcomingEvents(events)
loadUpcomingEventsPage(targetPage)
```

#### Teams Filtering
```javascript
applyTeamFilters()
```

#### Publications Filtering
```javascript
updatePubPagerState(page, totalPages)
renderPublications(pubs)
collectPubFilters()
loadPublicationsPage(targetPage)
debounce(fn, wait)
```

#### Existing Functions Enhanced
- `renderProjects()` - now displays funding type and members count
- Filter event handler now includes supervisor filter

---

## Database Considerations

### Tables Used
- `events` - for homepage events
- `projects` - for project listings with funding type
- `project_members` - for member counts per project
- `users` - for supervisors and team members
- `publications` - for publication listings
- `publication_authors` - for author filtering
- `equipment` - for equipment listings
- `reservations` - for equipment reservations
- `maintenances` - for equipment maintenance windows
- `team_members` - for team member filtering

### New Columns (No schema changes needed)
All required columns already exist in the database schema:
- `projects.type_financement`
- `equipment.etat`, `equipment.image_url`
- All necessary junction tables present

---

## Testing Checklist

- [ ] Homepage pagination loads events smoothly
- [ ] Teams filters work (client-side, no DB changes)
- [ ] Team publications link filters by team
- [ ] Projects show funding + members + supervisor filter works
- [ ] Dashboard shows equipment reservations with status
- [ ] Projects Edit button appears only for managers
- [ ] Publications filter/search/sort work via AJAX
- [ ] Equipment state computed correctly (libre/réservé/maintenance)
- [ ] Equipment reservation form validates date/time
- [ ] Usage stats show correct counts
- [ ] Offers page accessible from menu
- [ ] Event detail page displays correctly

---

## Performance Notes

- Pagination defaults: Events (3), Publications (6), Equipment (per-page form)
- Debounce on publication search: 300ms
- Complex queries use GROUP_CONCAT for aggregation
- Index recommendations: Add to `publications.date_publication`, `publications.type`, `equipment.etat`

---

## Migration Notes

If applying to existing database:
1. No schema changes required
2. Ensure sample data exists in `events`, `projects`, `publications`, `equipment`
3. Run seeding script from db-script.md if needed
4. Clear any browser cache for JavaScript updates
