# Phase 2: Component Refactoring - Progress Report

**Project:** Mini-Framework Architecture - Page Refactoring  
**Date:** January 2, 2026  
**Status:** 🔄 IN PROGRESS - Increments 7-13 Complete (57%)

---

## Overview

Refactoring all remaining pages (Projects, Equipment, Publications, Teams, Dashboard) from raw HTML templates into reusable Component classes while implementing new business logic.

---

## ✅ Completed Increments

### **Increment 7: Generic UI Primitives** ✅

**Files Created:**
1. `app/Components/PageHeader.php`
   - Renders page title with optional back button
   - Supports subtitle and action buttons
   - Props: `title`, `backUrl`, `backText`, `subtitle`, `actions[]`

2. `app/Components/Alert.php`
   - Success/Error/Warning/Info message banners
   - Dismissible option
   - Props: `message`, `type`, `dismissible`
   - Auto-styled based on type

3. `app/Components/FilterBar.php`
   - Flexible filter container supporting select/text/date inputs
   - Props: `filters[]`, `lang`, `showResetButton`
   - Each filter: `type`, `id`, `label`, `options`

**Benefits:**
- Eliminates duplicate alert/header code across all pages
- Centralized filter UI logic
- Easy to extend with new filter types

---

### **Increment 8: Projects Page Refactoring** ✅

**Files Created:**
1. `app/Components/ProjectCard.php`
   - Single project card with image, metadata, status badge
   - Smart status badge coloring (terminé=success, soumis=warning, etc.)
   - Props: `project[]`, `lang`, `baseUrl`
   - Includes data attributes for JS filtering

2. `app/Components/ProjectGrid.php`
   - Grid container for ProjectCard instances
   - Empty state handling ("Aucun projet trouvé")
   - Props: `projects[]`, `lang`, `baseUrl`, `emptyMessage`

**Files Modified:**
- `app/Views/Templates/project_list.phtml`
  - Reduced from **133 lines** to **~60 lines**
  - Now uses 3 clean component calls: `PageHeader` → `FilterBar` → `ProjectGrid`
  - All HTML logic moved to components

**Code Comparison:**
```php
// BEFORE: 80+ lines of raw HTML loops and conditionals

// AFTER:
$header = new PageHeader(['title' => $lang['projects_title']]);
echo $header->render();

$filterBar = new FilterBar(['filters' => $filters, 'lang' => $lang]);
echo $filterBar->render();

$projectGrid = new ProjectGrid(['projects' => $projects, 'lang' => $lang]);
echo $projectGrid->render();
```

---

### **Increment 9: Equipment Database & Model Logic** ✅

**Database Schema Updates:**
- Created `docs/equipment_reservation_schema.sql`
- **New Columns Added to `reservations` table:**
  - `is_urgent` TINYINT(1) - Flag for urgent reservations
  - `urgent_reason` TEXT - Explanation for urgency
  - `status` ENUM - Workflow states: pending, confirmed, conflict, rejected, finished
- **Indexes Added:**
  - `idx_reservations_equipment_dates` - Faster conflict detection
  - `idx_reservations_status` - Status filtering

**Files Modified:**
- `app/Models/EquipmentModel.php`

**New/Updated Methods:**

1. `createReservation()` - **CRITICAL CHANGE**
   - ✅ Removed blocking overlap check
   - ✅ Allows overlapping reservations
   - ✅ Accepts `$isUrgent` and `$urgentReason` parameters
   - ✅ Calls `detectAndMarkConflicts()` after insert
   - Returns: `['success' => bool, 'error' => string|null, 'reservation_id' => int|null]`

2. `detectAndMarkConflicts()` - **NEW**
   - Private method called after each reservation
   - Finds overlapping reservations for same equipment/time
   - Updates `status='conflict'` for all involved reservations
   - Automatic conflict detection - no admin intervention needed

3. `getEquipmentReservations()` - **NEW**
   - Retrieves all reservations for a specific equipment
   - Includes user info (name, email)
   - Optional date range filtering
   - Used for reservation history modal

4. `getUserReservations()` - **UPDATED**
   - Now includes `status`, `is_urgent`, `urgent_reason` fields
   - Backward compatible with `includeStatus` parameter

**Business Logic Flow:**
```
User submits reservation
  ↓
createReservation() inserts with status='confirmed'
  ↓
detectAndMarkConflicts() queries for overlaps
  ↓
If overlaps found:
  - New reservation → status='conflict'
  - Existing overlapping reservations → status='conflict'
  ↓
Return success with reservation_id
```

---

### **Increment 10: Equipment Page Refactoring** ✅

**Files Created:**
1. `app/Components/EquipmentCard.php`
   - Displays single equipment with image, metadata, status badge
   - Embeds ReservationForm component when user is authenticated
   - "Voir Planning" button triggers reservation history modal
   - Props: `equipment[]`, `lang`, `baseUrl`, `userId`
   - Smart status badge (libre=success, réservé=warning, maintenance=primary)

2. `app/Components/ReservationForm.php`
   - Reservation form with date/time inputs, motif field
   - **Urgent checkbox** - conditionally shows/hides urgent reason textarea
   - JavaScript `toggleUrgentReason()` function handles visibility
   - Makes textarea required only when checkbox is checked
   - Props: `equipId`, `baseUrl`, `lang`
   - Unique form IDs to support multiple forms on same page

3. `app/Components/ReservationHistoryModal.php`
   - Modal overlay component (hidden by default)
   - Global JavaScript functions: `showReservationHistory()`, `closeReservationHistory()`
   - AJAX fetches reservations via `Equipment::getReservations` endpoint
   - Renders reservation list with user names, date ranges
   - **Conflict status badges:** confirmed=green, conflict=red, pending=yellow, etc.
   - **Urgent badges:** Shows red "URGENT" badge + urgent reason in yellow box
   - Props: `baseUrl`

**Files Modified:**

1. `app/Views/Templates/equipment_list.phtml`
   - **Code Reduction:** 152 lines → ~85 lines (44% reduction)
   - Uses `PageHeader`, `Alert`, `FilterBar`, `EquipmentCard`, `ReservationHistoryModal`
   - Preserved usage statistics section (not componentized - static display)
   - Component-based equipment grid loop

2. `app/Controllers/EquipmentController.php`
   - Updated `reserve()` action:
     - Extracts `is_urgent` and `urgent_reason` from POST
     - Validates urgent reason if checkbox is checked
     - Passes urgent parameters to `createReservation()`
   - **NEW** `getReservations()` action:
     - AJAX endpoint returning JSON
     - Returns equipment name + full reservation history
     - Used by ReservationHistoryModal

3. `app/Models/EquipmentModel.php`
   - Updated `getEquipmentReservations()`:
     - Adds combined `user_name` field (prenom + nom)
     - Fallback to email if name empty
     - Simplifies modal rendering

**Code Comparison - equipment_list.phtml:**
```php
// BEFORE: 152 lines with nested HTML
<h1 class="section-title"><?= $lang['equipment_title'] ?></h1>
<?php if (!empty($success)): ?>
  <div style="background: var(--bg-light);">...</div>
<?php endif; ?>
<!-- ... 40+ lines of filter form HTML ... -->
<!-- ... 80+ lines of equipment card loops ... -->

// AFTER: 85 lines with components
<?php
$pageHeader = new PageHeader(['title' => $lang['equipment_title']]);
echo $pageHeader->render();

$successAlert = new Alert(['message' => $success, 'type' => 'success']);
echo $successAlert->render();

$filterBar = new FilterBar(['filters' => [...], 'lang' => $lang]);
echo $filterBar->render();

foreach ($equipment as $equip) {
    $card = new EquipmentCard([
        'equipment' => $equip,
        'userId' => $_SESSION['user_id'] ?? null
    ]);
    echo $card->render();
}

$modal = new ReservationHistoryModal(['baseUrl' => BASE_URL]);
echo $modal->render();
?>
```

**Key Features Implemented:**
- ✅ Urgent checkbox shows/hides reason textarea dynamically
- ✅ "Voir Planning" button on each card opens modal
- ✅ Modal fetches reservation history via AJAX
- ✅ Conflict status displayed with red badges
- ✅ Urgent reservations highlighted with yellow background boxes
- ✅ User names concatenated from first/last name with email fallback
- ✅ Controller validates urgent reason if checkbox is checked
- ✅ Model includes all new status/urgent fields in responses

**Testing Checklist:**
- [ ] Equipment page loads with PageHeader/FilterBar/EquipmentCard components
- [ ] Urgent checkbox shows textarea when checked, hides when unchecked
- [ ] Textarea becomes required only when urgent checkbox is checked
- [ ] Submitting urgent reservation without reason shows error
- [ ] Submitting urgent reservation with reason succeeds
- [ ] "Voir Planning" button opens modal with loading indicator
- [ ] Modal displays all reservations with correct user names
- [ ] Conflict status badges appear in red for overlapping reservations
- [ ] Urgent badges appear in red with yellow reason boxes
- [ ] Overlapping reservations both get marked as conflict in database
- [ ] Modal closes when clicking X button or outside modal area

---

### **Increment 11: Publications Database Single Author** ✅

**Database Schema Changes:**
- Created `docs/publications_single_author_migration.sql`
- **Migration:** Changed from many-to-many (publication_authors junction table) to one-to-one (author_id FK)
- **New Column:** `publications.author_id INT` with FK constraint to `users(id_user)`
- **Data Migration:** Takes first author from `publication_authors` for each publication
- **Indexes:** Added `idx_publications_author` for performance
- **Optional Cleanup:** Junction table `publication_authors` can be dropped after migration

**Files Modified:**

1. `app/Models/PublicationModel.php` - **5 Methods Updated:**
   - `getAuthors()` - Changed FROM clause:
     ```php
     // BEFORE: FROM publication_authors pa JOIN users u
     // AFTER: FROM publications p JOIN users u ON p.author_id = u.id_user
     ```
   - `searchPublications()` - Simplified query:
     - Removed `GROUP_CONCAT(...)` aggregation
     - Changed to single author: `CONCAT(u.prenom, ' ', u.nom) as auteur`
     - Removed `GROUP BY` clause (no longer needed)
     - Direct JOIN: `LEFT JOIN users u ON p.author_id = u.id_user`
   
   - `buildWhereClause()` - Author filter simplified:
     ```php
     // BEFORE: WHERE EXISTS (SELECT 1 FROM publication_authors pa WHERE pa.id_pub = p.id_pub AND pa.id_user = :author)
     // AFTER: WHERE p.author_id = :author
     ```
   
   - `countPublications()` - Removed DISTINCT:
     ```php
     // BEFORE: SELECT COUNT(DISTINCT p.id_pub)
     // AFTER: SELECT COUNT(p.id_pub)
     ```
   
   - `getAllPublications()` - Updated JOIN (similar to searchPublications)

2. `app/Models/TeamModel.php` - **1 Method Updated:**
   - `getUserPublications()` - Removed junction table:
     ```php
     // BEFORE: FROM publications p JOIN publication_authors pa ON p.id_pub = pa.id_pub WHERE pa.id_user = :user_id
     // AFTER: FROM publications p WHERE p.author_id = :user_id
     ```

**Business Logic Impact:**
- **One Author Per Publication:** Enforces single-author rule as per requirements
- **Simplified Queries:** No more GROUP BY, DISTINCT, or GROUP_CONCAT needed
- **Performance:** Faster queries with direct FK join instead of junction table
- **Data Consistency:** FK constraint ensures author exists in users table

**Migration SQL Key Sections:**
```sql
-- Add new column
ALTER TABLE publications ADD COLUMN author_id INT NULL;

-- Migrate data (take first author)
UPDATE publications p
SET author_id = (
    SELECT pa.id_user 
    FROM publication_authors pa 
    WHERE pa.id_pub = p.id_pub 
    LIMIT 1
);

-- Add constraint
ALTER TABLE publications 
ADD CONSTRAINT fk_publications_author 
FOREIGN KEY (author_id) REFERENCES users(id_user);

-- Optional cleanup
-- DROP TABLE publication_authors;
```

---

### **Increment 12: Publications & Dashboard Refactoring** ✅

**Files Created:**

1. `app/Components/PublicationItem.php` (115 lines)
   - Horizontal list item for publications
   - Displays: Title, **single author** (prenom + nom), year, abstract excerpt, DOI link, PDF download
   - Props: `publication[]`, `lang`, `truncateAbstract` (default: 250)
   - **Truncation:** Automatically truncates abstract to specified length with "..."
   - **Empty Field Handling:** Shows "N/A" with `empty-field` class for missing DOI/PDF
   - **Single Author Display:** Changed from `pub_authors` (plural) to `pub_author` (singular) lang key

2. `app/Components/DashboardStat.php` (60 lines)
   - Counter widget with optional icon and color
   - Props: `label`, `value`, `icon` (optional), `color` (default: var(--primary-color)), `href` (optional)
   - **Clickable Stats:** When `href` provided, wraps in anchor tag with hover effects
   - **Icon Support:** Displays emoji or symbol before value
   - **Styling:** Rounded corners, shadow, hover scale effect for links

**Files Modified:**

1. `app/Views/Templates/publication_list.phtml`
   - **Code Reduction:** 158 lines → 110 lines (30% reduction)
   - Uses: `PageHeader`, `FilterBar`, `PublicationItem`
   - **Preserved:** Filter bar with AJAX logic (not componentized - has custom JavaScript)
   - Component-based publication loop:
     ```php
     foreach ($publications as $pub) {
         $pubItem = new PublicationItem([
             'publication' => $pub,
             'lang' => $lang,
             'truncateAbstract' => 250
         ]);
         echo $pubItem->render();
     }
     ```

2. `app/Views/Templates/dashboard.phtml`
   - **Code Reduction:** 218 lines → 170 lines (22% reduction)
   - **NEW Section:** Top stats with 3 DashboardStat widgets
     - Projects counter (📁 icon) → links to `#projects-section`
     - Publications counter (📄 icon) → links to `#publications-section`
     - Reservations counter (🔧 icon) → links to `#reservations-section`
   
   - **Component Reusability Demonstrated:**
     - Projects section: Uses `ProjectCard` component (same as project_list.phtml!)
     - Publications section: Uses `PublicationItem` component (same as publication_list.phtml!)
   
   - **Data Preparation:** Adds current user's full name to publications array:
     ```php
     foreach ($publications as &$pub) {
         $pub['auteur'] = $currentUser['prenom'] . ' ' . $currentUser['nom'];
     }
     ```

**Code Comparison - dashboard.phtml:**
```php
// BEFORE: 218 lines with inline HTML stats and project/publication loops

// AFTER: 170 lines with clean component calls

// Top Stats Section
$projectsCountStat = new DashboardStat([
    'label' => $lang['dashboard_projects_count'],
    'value' => count($projects),
    'icon' => '📁',
    'href' => '#projects-section'
]);
echo $projectsCountStat->render();

// Projects Section - REUSES ProjectCard!
foreach ($projects as $project) {
    $projectCard = new ProjectCard([
        'project' => $project,
        'lang' => $lang,
        'baseUrl' => BASE_URL
    ]);
    echo $projectCard->render();
}

// Publications Section - REUSES PublicationItem!
foreach ($publications as $pub) {
    $pubItem = new PublicationItem([
        'publication' => $pub,
        'lang' => $lang,
        'truncateAbstract' => 150  // Shorter for dashboard
    ]);
    echo $pubItem->render();
}
```

**Reusability Achievement:**
- ✅ **ProjectCard:** Used in 2 places (project_list + dashboard)
- ✅ **PublicationItem:** Used in 2 places (publication_list + dashboard)
- ✅ **DashboardStat:** Used 3 times in dashboard (projects, publications, reservations)

**Key Features:**
- Dashboard stats are clickable anchor links to respective sections
- Publications show current user's name (not team publications)
- Truncation length is configurable (250 for full page, 150 for dashboard)
- Empty fields handled gracefully with "N/A" display

---

### **Increment 13: Teams & Members Refactoring** ✅

**Files Created:**

1. `app/Components/MemberCard.php` (95 lines)
   - Team member card component
   - **Photo Display:** Shows member photo OR initials placeholder (first letter of first/last name)
   - **Leader Styling:** When `isLeader=true`, adds border and "Chef d'équipe" badge
   - Props: `member[]`, `lang`, `baseUrl`, `isLeader` (bool), `showEmail` (bool)
   - **Action Buttons:**
     - "Voir le profil" → Links to member profile page
     - "Contact" → mailto link (only shown if showEmail=true and email exists)
   - **Role Badge:** Displays `role_dans_equipe` (e.g., "Doctorant", "Post-doc") if set

2. `app/Components/TeamSection.php` (90 lines)
   - Complete team section component
   - **Team Header:** Displays team name, description, "Voir les publications" link
   - **Leader Section:** Extracts leader data from team array (`chef_*` fields), renders with MemberCard (isLeader=true)
   - **Members Grid:** Loops through `members[]` array, renders each with MemberCard
   - Props: `team[]`, `lang`, `baseUrl`, `showPublicationsLink` (bool, default: true)
   - **Data Requirements:** Expects team array with: `nom`, `description`, `chef_id`, `chef_prenom`, `chef_nom`, `chef_grade`, `chef_photo`, `chef_email`, `members[]`

**Files Modified:**

1. `app/Views/Templates/team_list.phtml`
   - **Code Reduction:** 179 lines → 93 lines (48% reduction)
   - Uses: `PageHeader`, `FilterBar`, `TeamSection`
   - **Preserved:** Grade filter building logic + JavaScript filter/sort functionality
   - **Simplified Team Loop:**
     ```php
     foreach ($teams as $team) {
         $teamSection = new TeamSection([
             'team' => $team,
             'lang' => $lang,
             'baseUrl' => BASE_URL,
             'showPublicationsLink' => true
         ]);
         echo $teamSection->render();
     }
     ```
   - **Fixed:** File corruption issue during refactoring (removed duplicate HTML)

**Component Nesting:**
```
TeamSection
  ├── Team Header (name, description, publications link)
  ├── Leader: MemberCard (isLeader=true)
  │     ├── Photo/Initials
  │     ├── Leader Badge
  │     ├── Name + Grade
  │     └── Action Buttons
  └── Members Grid
        └── MemberCard (foreach member)
              ├── Photo/Initials
              ├── Name + Grade
              ├── Role Badge (if role_dans_equipe set)
              └── Action Buttons
```

**Key Features:**
- **Initials Fallback:** When no photo, displays circular badge with member initials
- **Leader Distinction:** Leader card has special border and badge
- **Role Badges:** Displays member's role within team (e.g., "Doctorant")
- **Responsive Grid:** Members displayed in CSS grid layout
- **Filter Compatibility:** Preserved data attributes for JavaScript filtering

**Testing Checklist:**
- [ ] Teams page displays all teams using TeamSection component
- [ ] Each team shows leader with special styling (border + badge)
- [ ] Members without photos show initials placeholder
- [ ] Role badges display for members with `role_dans_equipe`
- [ ] "Voir les publications de l'équipe" links to filtered publications page
- [ ] Filter by team/grade dropdowns still work with JavaScript
- [ ] Sort by name/grade functionality preserved

---

## 📊 Progress Summary

### Completed: 13 / 23 Increments (57%)

| Increment | Status | Description |
|-----------|--------|-------------|
| 1-6 (Phase 1) | ✅ | Component core, Navbar, RBAC, Dynamic Home |
| 7 | ✅ | Generic UI Primitives (PageHeader, Alert, FilterBar) |
| 8 | ✅ | Projects Page (ProjectCard, ProjectGrid) |
| 9 | ✅ | Equipment Database & Conflict Logic |
| 10 | ✅ | Equipment Page Components |
| 11 | ✅ | Publications Database (Single Author) |
| 12 | ✅ | Publications & Dashboard Refactoring |
| 13 | ✅ | Teams & Members Refactoring |

---

### **Increment 14: Event Detail Page Refactoring** ✅

**Files Created:**

1. `app/Components/EventCard.php` (120 lines)
   - Event detail display component with split layout
   - **Left Column:** Event image (with placeholder fallback) + description section
   - **Right Column:** Metadata sidebar with date, location, type
   - **Enhanced UI:** Added emoji icons (📅 date, 📍 location, 🏷️ type) for visual clarity
   - **Styling Improvements:** Border-radius, box-shadows, dividers between metadata sections
   - Props: `event[]` (required), `lang`, `baseUrl`
   - **Data Handling:** 
     - Graceful fallback for missing description ("Aucune description disponible")
     - Date formatting with try-catch error handling
     - Conditional location rendering (only if lieu is set)

**Files Modified:**

1. `app/Views/Templates/event_detail.phtml`
   - **Code Reduction:** 51 lines → 26 lines (49% reduction)
   - Uses: `PageHeader` (with back button), `EventCard`
   - **Simplified Structure:**
     ```php
     // BEFORE: 51 lines with inline HTML for header, image, description, metadata
     
     // AFTER: 26 lines with clean component calls
     $pageHeader = new PageHeader([
         'title' => $event['titre'],
         'backUrl' => BASE_URL . 'index.php?controller=Home&action=index',
         'backText' => $lang['back'],
         'lang' => $lang
     ]);
     echo $pageHeader->render();
     
     $eventCard = new EventCard([
         'event' => $event,
         'lang' => $lang,
         'baseUrl' => BASE_URL
     ]);
     echo $eventCard->render();
     ```

**Key Features:**
- **PageHeader Reusability:** Uses existing PageHeader component with back button
- **Responsive Grid Layout:** 2fr/1fr split (main content / sidebar)
- **Image Error Handling:** Fallback to event-placeholder.jpg on image load failure
- **Empty State Handling:** Italic gray text when description is missing
- **Enhanced Metadata Display:** Icons, dividers, and better visual hierarchy
- **Date Formatting:** Converts database timestamp to readable format (d/m/Y H:i)

**Testing Checklist:**
- [ ] Event detail page loads with PageHeader component showing event title
- [ ] Back button navigates to home page
- [ ] Event image displays correctly with proper fallback
- [ ] Description shows formatted text with line breaks preserved (nl2br)
- [ ] Missing description shows "Aucune description disponible" in italic
- [ ] Date displays in correct format (DD/MM/YYYY HH:MM)
- [ ] Location only appears if event has lieu field
- [ ] Type badge displays with correct styling
- [ ] Metadata sidebar has proper icons and dividers
- [ ] Layout is responsive (2-column grid on desktop)

---

## 📊 Progress Summary

### Completed: 14 / 23 Increments (61%)

| Increment | Status | Description |
|-----------|--------|-------------|
| 1-6 (Phase 1) | ✅ | Component core, Navbar, RBAC, Dynamic Home |
| 7 | ✅ | Generic UI Primitives (PageHeader, Alert, FilterBar) |
| 8 | ✅ | Projects Page (ProjectCard, ProjectGrid) |
| 9 | ✅ | Equipment Database & Conflict Logic |
| 10 | ✅ | Equipment Page Components |
| 11 | ✅ | Publications Database (Single Author) |
| 12 | ✅ | Publications & Dashboard Refactoring |
| 13 | ✅ | Teams & Members Refactoring |
| 14 | ✅ | Event Detail Page Refactoring |

---

## 🚀 Next Steps

### **Increment 15: Member Profile Pages** (Next)
**Components:**
- `ProfileCard.php` - User profile display with photo and info
- Update `member_profile.phtml` with ProfileCard

---

### **Increment 16-17: Members Directory & Profile Edit** (Pending)
**Components:**
- `ProfileCard.php` - User profile display
- `PublicationList.php` - Reusable publication list
- Update `member_profile.phtml`, `members_directory.phtml`, `profile_edit.phtml`

---

### **Increment 18-20: Offers & Organigram** (Pending)
**Components:**
- `OfferCard.php` - Job offer display
- `Organigram.php` - Organizational chart tree
- Update `offers.phtml`, `presentation.phtml`

---

### **Increment 21-23: Authentication & Final Polish** (Pending)
- Refactor `login.phtml` and `contact.phtml`
- Add transition animations
- Performance optimization

---

## 📁 New File Structure

```
app/
├── Components/
│   ├── Alert.php                    ← (Increment 7)
│   ├── DashboardStat.php           ← NEW (Increment 12)
│   ├── EquipmentCard.php           ← NEW (Increment 10)
│   ├── EventCard.php               ← NEW (Increment 14)
│   ├── FilterBar.php               ← (Increment 7)
│   ├── LabOverview.php             (Increment 6)
│   ├── MemberCard.php              ← NEW (Increment 13)
│   ├── Navbar.php                  (Increment 2)
│   ├── PageHeader.php              ← (Increment 7)
│   ├── Partners.php                (Increment 6)
│   ├── ProjectCard.php             ← (Increment 8)
│   ├── ProjectGrid.php             ← (Increment 8)
│   ├── PublicationItem.php         ← NEW (Increment 12)
│   ├── ReservationForm.php         ← NEW (Increment 10)
│   ├── ReservationHistoryModal.php ← NEW (Increment 10)
│   ├── ScientificUpdates.php       (Increment 6)
│   ├── Slideshow.php               (Increment 6)
│   ├── TeamSection.php             ← NEW (Increment 13)
│   └── UpcomingEvents.php          (Increment 6)
│
├── Models/
│   ├── EquipmentModel.php          ← MODIFIED (Increment 9, 10)
│   ├── PublicationModel.php        ← MODIFIED (Increment 11)
│   └── TeamModel.php               ← MODIFIED (Increment 11)
│
├── Controllers/
│   └── EquipmentController.php     ← MODIFIED (Increment 10)
│
└── Views/Templates/
    ├── dashboard.phtml             ← MODIFIED (Increment 12)
    ├── equipment_list.phtml        ← MODIFIED (Increment 10)
    ├── event_detail.phtml          ← MODIFIED (Increment 14)
    ├── home.phtml                  ← MODIFIED (Increment 6)
    ├── layout.phtml                ← MODIFIED (Increment 2)
    ├── project_list.phtml          ← MODIFIED (Increment 8)
    ├── publication_list.phtml      ← MODIFIED (Increment 12)
    └── team_list.phtml             ← MODIFIED (Increment 13)

docs/
├── equipment_reservation_schema.sql           ← NEW (Increment 9)
└── publications_single_author_migration.sql   ← NEW (Increment 11)
```

---

## 🧪 Testing Checklist

### Increment 7 Tests:
- [x] PageHeader displays with back button
- [x] Alert shows correct colors for success/error/warning/info
- [x] FilterBar renders with multiple select/text inputs
- [x] Reset button clears all filters

### Increment 8 Tests:
- [x] Projects page loads using new components
- [x] Filter bar filters projects by domain/status/supervisor
- [x] Project cards display correct status badge colors
- [x] Empty state shows when no projects match filters
- [x] "Voir les publications" links work

### Increment 9 Tests:
- [ ] Run SQL migration: `docs/equipment_reservation_schema.sql`
- [ ] Create overlapping reservations → both marked as 'conflict'
- [ ] Create urgent reservation with reason → fields saved correctly
- [ ] `getEquipmentReservations()` returns reservation history
- [ ] Maintenance periods still block reservations

### Increment 10 Tests:
- [ ] Equipment page loads with PageHeader/FilterBar/EquipmentCard components
- [ ] Urgent checkbox shows textarea when checked, hides when unchecked
- [ ] Textarea becomes required only when urgent checkbox is checked
- [ ] Submitting urgent reservation without reason shows error
- [ ] Submitting urgent reservation with reason succeeds
- [ ] "Voir Planning" button opens modal with loading indicator
- [ ] Modal displays all reservations with correct user names
- [ ] Conflict status badges appear in red for overlapping reservations
- [ ] Urgent badges appear in red with yellow reason boxes
- [ ] Overlapping reservations both get marked as conflict in database
- [ ] Modal closes when clicking X button or outside modal area

### Increment 11 Tests:
- [ ] Run SQL migration: `docs/publications_single_author_migration.sql`
- [ ] Publications page shows single author (not comma-separated list)
- [ ] PublicationModel methods return single author field
- [ ] Author filter in publications page works with single author
- [ ] TeamModel->getUserPublications() returns user's publications correctly

### Increment 12 Tests:
- [ ] Publications page uses PublicationItem component
- [ ] Dashboard stats are clickable and navigate to sections
- [ ] Dashboard projects section uses ProjectCard (reusability test)
- [ ] Dashboard publications section uses PublicationItem (reusability test)
- [ ] Abstract truncation works (250 chars in publications, 150 in dashboard)
- [ ] Empty DOI/PDF fields show "N/A" with correct styling

### Increment 13 Tests:
- [ ] Teams page displays all teams using TeamSection component
- [ ] Each team shows leader with special styling (border + badge)
- [ ] Members without photos show initials placeholder
- [ ] Role badges display for members with `role_dans_equipe`
- [ ] "Voir les publications de l'équipe" links to filtered publications page
- [ ] Filter by team/grade dropdowns still work with JavaScript
- [ ] Sort by name/grade functionality preserved

**SQL Test:**
```sql
-- Verify schema changes
DESCRIBE reservations;

-- Test conflict detection
INSERT INTO reservations (user_id, equip_id, date_debut, date_fin, motif, status)
VALUES (1, 1, '2026-01-10 10:00:00', '2026-01-10 12:00:00', 'Test 1', 'confirmed');

INSERT INTO reservations (user_id, equip_id, date_debut, date_fin, motif, status)
VALUES (2, 1, '2026-01-10 11:00:00', '2026-01-10 13:00:00', 'Test 2', 'confirmed');

-- Both should now have status='conflict'
SELECT id_res, user_id, date_debut, date_fin, status FROM reservations WHERE equip_id = 1;
```

---

## 💡 Key Achievements So Far

1. **Code Reduction:**
   - `home.phtml`: 246 lines → 20 lines (92% reduction)
   - `project_list.phtml`: 133 lines → 60 lines (55% reduction)
   - `equipment_list.phtml`: 152 lines → 85 lines (44% reduction)
   - `publication_list.phtml`: 158 lines → 110 lines (30% reduction)
   - `dashboard.phtml`: 218 lines → 170 lines (22% reduction)
   - `team_list.phtml`: 179 lines → 93 lines (48% reduction)
   - `event_detail.phtml`: 51 lines → 26 lines (49% reduction)

2. **Component Reusability Demonstrated:**
   - **PageHeader:** Used in 7+ templates (including event_detail) ✅
   - **Alert:** Used in 4+ templates
   - **FilterBar:** Used in 4 templates (Projects, Equipment, Publications, Teams)
   - **ProjectCard:** Used in 2 places (project_list + dashboard) ✅
   - **PublicationItem:** Used in 2 places (publication_list + dashboard) ✅
   - **MemberCard:** Used in 2 contexts (standalone + within TeamSection) ✅

3. **Business Logic Improvements:**
   - Equipment conflict detection is now automatic
   - Urgent reservations supported with reason tracking
   - Publications simplified to single author (enforced at DB level)
   - No more blocking of overlapping equipment reservations

4. **Database Schema Enhancements:**
   - Equipment: Added `status`, `is_urgent`, `urgent_reason` fields with indexes
   - Publications: Changed from many-to-many to one-to-one author relationship
   - Performance: Added strategic indexes for faster queries

5. **Maintainability:**
   - All UI logic in PHP classes (not templates)
   - Easy to modify component behavior without touching views
   - Consistent prop naming across all components
   - DRY principle: Write once, use everywhere

---

## 🎯 Estimated Completion

- **Increments 15-17:** Profile + Members pages (Est. 3-4 hours)
- **Increments 18-20:** Offers + Organigram (Est. 3 hours)
- **Increments 21-23:** Auth + Polish (Est. 2 hours)

**Total Remaining:** ~8-9 hours

**Phase 2 Progress:** 14/23 Increments (61% complete)

---

**Current Status:** Ready to proceed with Increment 15 (Member Profile Page)

**SQL Migrations to Run Before Testing:**
```bash
# Equipment reservation schema (Increment 9)
mysql -u root -p your_database < docs/equipment_reservation_schema.sql

# Publications single author migration (Increment 11)
mysql -u root -p your_database < docs/publications_single_author_migration.sql
```

**Next Immediate Action:** Implement EventCard component and refactor event_detail.phtml
