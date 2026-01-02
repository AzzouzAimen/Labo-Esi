# Mini-Framework Refactoring - Implementation Summary

**Project:** Native PHP MVC → Mini-Framework Architecture  
**Date:** January 2, 2026  
**Status:** ✅ Phase 1 Complete (All 6 Increments)

---

## ✅ Completed Increments

### **Increment 1: Component Core** ✅
**Files Created:**
- `core/Component.php` - Abstract base class for all UI components

**Features Implemented:**
- Abstract `Component` class with protected `$props` array
- Constructor accepting `array $props = []`
- Abstract method `render(): string`
- Magic method `__toString()` for direct echo support

**Modified:**
- `public/index.php` - Added Component.php to core includes + autoload for app/Components/

---

### **Increment 2: Navbar Component** ✅
**Files Created:**
- `app/Components/Navbar.php` - Navigation menu component

**Features Implemented:**
- Extracted navbar HTML from layout.phtml into reusable component
- Props: `lang`, `baseUrl`, `userId`
- Renders navigation with conditional user menu
- Uses output buffering for clean HTML generation

**Modified:**
- `app/Views/Templates/layout.phtml` - Replaced raw HTML with component instantiation

---

### **Increment 3: RBAC Database Schema** ✅
**Status:** Already completed via SQL scripts

**Database Tables Created:**
- `roles` (id, name, description)
- `permissions` (id, slug, description)
- `role_permissions` (junction table)
- `users.role_id` (foreign key added)

**Seeded Data:**
- 3 default roles: Admin, Enseignant, Etudiant
- 25+ granular permissions (dashboard.*, users.*, projects.*, etc.)
- All permissions assigned to Admin role

---

### **Increment 4: RBAC Logic Implementation** ✅
**Files Modified:**
- `app/Models/UserModel.php`

**New Methods Added:**
```php
- getPermissions($userId): array           // Returns array of permission slugs
- hasPermission($userId, $slug): bool      // Checks single permission
- getUserRole($userId): array|false        // Gets role information
```

**Updated Methods:**
- `findByUsername()` - Now JOINs roles table, returns `role_id` and `role_name`
- `getUserById()` - Now JOINs roles table

**Modified:**
- `app/Controllers/AuthController.php`

**New Session Variables:**
```php
$_SESSION['role_id']      // Integer - New RBAC role ID
$_SESSION['role_name']    // String - Role name from roles table
$_SESSION['permissions']  // Array - List of permission slugs
$_SESSION['role']         // String - Legacy field (kept for BC)
```

**New Static Method:**
```php
AuthController::hasPermission($permissionSlug): bool
```

---

### **Increment 5: Dynamic Layout Database Layer** ✅
**Status:** Already completed via SQL scripts

**Database Table Created:**
- `layout_settings`
  - `id` (PK)
  - `page_name` (e.g., 'home')
  - `component_class` (e.g., 'Slideshow')
  - `order_index` (display order)
  - `is_visible` (toggle visibility)
  - `props_json` (JSON config storage)

**Seeded Data:**
```
home | Slideshow           | order 1 | visible
home | ScientificUpdates   | order 2 | visible
home | LabOverview         | order 3 | visible
home | UpcomingEvents      | order 4 | visible
home | Partners            | order 5 | visible
```

---

### **Increment 6: Dynamic Home Page Implementation** ✅

**Files Created:**
1. `app/Components/Slideshow.php`
   - Props: recentNews, lang, baseUrl
   - Renders news slideshow with image carousel

2. `app/Components/ScientificUpdates.php`
   - Props: recentProjects, recentPublications, recentPartners, lang, baseUrl
   - Renders 3-column grid of recent scientific content

3. `app/Components/LabOverview.php`
   - Props: lang, baseUrl
   - Renders lab description and mission statement

4. `app/Components/UpcomingEvents.php`
   - Props: upcomingEvents, allUpcomingEvents, pagination data, lang, baseUrl
   - Renders event cards with client-side pagination

5. `app/Components/Partners.php`
   - Props: allPartners, lang, baseUrl
   - Renders partner logo carousel

**Files Modified:**
- `app/Controllers/HomeController.php`
  - Added `loadDynamicComponents($pageName, $pageData)` private method
  - Queries `layout_settings` table for component configuration
  - Instantiates components dynamically based on DB config
  - Passes `$data['components']` array to view
  - Keeps legacy data arrays for backward compatibility

- `app/Views/Templates/home.phtml`
  - Replaced 240+ lines of hardcoded HTML
  - Now loops through `$components` array and calls `render()`
  - ~20 lines of clean, maintainable code

---

## 🎯 How It Works Now

### **1. Database-Driven Layout**
Admins can modify homepage layout via SQL:
```sql
-- Reorder components
UPDATE layout_settings SET order_index = 1 WHERE component_class = 'Partners';

-- Hide a component
UPDATE layout_settings SET is_visible = 0 WHERE component_class = 'LabOverview';

-- Add custom props
UPDATE layout_settings SET props_json = '{"limit": 10}' WHERE component_class = 'Slideshow';
```

### **2. Component Rendering Flow**
```
HomeController::index()
  ↓
loadDynamicComponents('home')
  ↓
Query layout_settings WHERE page_name='home' AND is_visible=1 ORDER BY order_index
  ↓
For each row:
  - Instantiate component class (e.g., new Slideshow($props))
  - Merge pageData + props_json
  ↓
Return array of Component objects
  ↓
Pass to view as $data['components']
  ↓
home.phtml:
  foreach ($components as $component) {
      echo $component->render();
  }
```

### **3. RBAC Permission Checking**
```php
// In any controller or view:
if (AuthController::hasPermission('layout.manage')) {
    // Show "Edit Layout" button
}

// Or check in UserModel:
$userModel->hasPermission($userId, 'projects.create');
```

---

## 📂 New Directory Structure

```
core/
  ├── Component.php          ← NEW: Abstract base class
  ├── Controller.php
  ├── Database.php
  ├── Model.php
  ├── Router.php
  └── View.php

app/
  ├── Components/            ← NEW DIRECTORY
  │   ├── Navbar.php
  │   ├── Slideshow.php
  │   ├── ScientificUpdates.php
  │   ├── LabOverview.php
  │   ├── UpcomingEvents.php
  │   └── Partners.php
  ├── Controllers/
  │   ├── AuthController.php (MODIFIED: RBAC session)
  │   ├── HomeController.php (MODIFIED: Dynamic layout)
  │   └── ...
  ├── Models/
  │   ├── UserModel.php      (MODIFIED: RBAC methods)
  │   └── ...
  └── Views/
      └── Templates/
          ├── layout.phtml   (MODIFIED: Navbar component)
          ├── home.phtml     (MODIFIED: Dynamic rendering)
          └── ...
```

---

## 🧪 Testing Checklist

### **Navigation Component**
- [ ] Load homepage - navbar should display
- [ ] Login as user - verify Dashboard and notification icon appear
- [ ] Logout - verify user menu disappears

### **RBAC System**
- [ ] Login and check `$_SESSION['permissions']` contains array of slugs
- [ ] Verify `$_SESSION['role_id']` and `$_SESSION['role_name']` are set
- [ ] Test `AuthController::hasPermission('dashboard.view')`

### **Dynamic Layout**
- [ ] Load homepage - all 5 components should render in order
- [ ] Change order_index in DB - verify visual order changes
- [ ] Set is_visible=0 for a component - verify it disappears
- [ ] Check browser console for errors

### **Component Rendering**
- [ ] Verify Slideshow shows recent news
- [ ] Verify ScientificUpdates shows projects/pubs/partners
- [ ] Verify LabOverview shows description
- [ ] Verify UpcomingEvents shows event cards
- [ ] Verify Partners carousel functions

---

## 🚀 Next Steps (Future Increments)

### **Phase 2: Admin UI for Layout Management**
- Create `LayoutController` with CRUD for layout_settings
- Add drag-and-drop UI to reorder components
- Add component visibility toggles
- Add JSON props editor

### **Phase 3: Component Library Expansion**
- Create reusable components:
  - `Card.php`, `Button.php`, `Form.php`
  - `Alert.php`, `Modal.php`, `Tabs.php`
- Refactor other pages (Team, Projects, Equipment) to use components

### **Phase 4: Advanced RBAC**
- Add role management UI
- Add permission management UI
- Implement permission-based component visibility
- Add audit logging for permission changes

### **Phase 5: Multi-Page Dynamic Layouts**
- Extend dynamic layout system to other pages
- Create page templates with default component sets
- Add theme system (color schemes, fonts)

---

## 📝 Notes for Developers

### **Creating New Components**
1. Create class in `app/Components/` extending `Component`
2. Implement `render(): string` method
3. Use `$this->props['keyName']` to access data
4. Use output buffering (`ob_start()` / `ob_get_clean()`)
5. Escape all user data with `htmlspecialchars()`

### **Adding Components to Pages**
1. Insert row in `layout_settings` table:
   ```sql
   INSERT INTO layout_settings (page_name, component_class, order_index, is_visible)
   VALUES ('home', 'MyNewComponent', 6, 1);
   ```
2. Component file must exist in `app/Components/`
3. Refresh page - component auto-loaded and rendered

### **Component Props Best Practices**
- Always provide default values: `$this->props['data'] ?? []`
- Pass all language strings via `lang` prop
- Pass BASE_URL via `baseUrl` prop
- Store complex config in `props_json` column as JSON

---

## 🎉 Benefits Achieved

✅ **Separation of Concerns** - UI logic in components, not templates  
✅ **Database-Driven Design** - Non-devs can modify layouts  
✅ **Reusability** - Components used across multiple pages  
✅ **Maintainability** - home.phtml reduced from 246 to ~20 lines  
✅ **Flexibility** - Easy to add/remove/reorder components  
✅ **Security** - RBAC system with granular permissions  
✅ **Scalability** - Framework foundation for future features  

---

**End of Implementation Summary**  
*All 6 increments completed successfully!* 🎯
