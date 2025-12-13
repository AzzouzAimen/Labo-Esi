# Project Specifications: Web Application for University Laboratory Management
**Module:** TDW 2CS | **Year:** 2025/2026

## 1. Technical Constraints & Rules (Strict Adherence)
*Failure to follow these results in direct grade penalties.*

*   **Architecture:** Strict **MVC (Model-View-Controller)**.
    *   **Requirement:** Logic, Data, and Presentation must be separated.
    *   **Requirement:** All components must be **Classes**, including the **View**.
*   **Tech Stack:**
    *   Backend: PHP (Native, no frameworks like Laravel/Symfony).
    *   Frontend: HTML5, CSS3, JavaScript, jQuery.
    *   Database: MySQL.
    *   AJAX: Required for dynamic interactions (filtering, slideshows).
*   **Libraries:** **No external libraries** allowed (except jQuery) without prior teacher approval.
*   **Work Mode:** Individual project. (Collaboration = 0/20).
*   **AI Usage:** Code explanation is required. Blind use of AI is prohibited.

## 2. Database & Configuration
*   **Database Name:** `TDW`
*   **Seeding:** Minimum **3 entries (rows)** per table is required.
*   **Required Credentials (Penalties apply if missing):**

| Account Type | Username | Password |
| :--- | :--- | :--- |
| **Admin** | `admin` | `admin` |
| **Standard User** | `user` | `user` |

## 3. Part I: Front Office (Public Interface)
*Target Audience: Visitors, Students, Researchers, Partners.*

### A. Global Elements
*   **Header:** Lab Logo (Left), Social Links & University Link (Right).
*   **Navigation:** Horizontal Menu (Home, Projects, Publications, Equipment, Members, Contact).
*   **Footer:** Contact info, Simplified Menu, University Logo.
*   **Language:** Text content should be variable-based (prepared for multilingual support).

### B. Homepage
*   **Slideshow:**
    *   Content: News (Projects, Pubs, Events).
    *   Logic: **Dynamic** (from DB) + **Automatic scroll** (every 5 seconds) + Clickable link to details.
*   **Main Content:** Recent news & Upcoming events.

### C. Content Area Sections
1.  **Scientific News:** New projects, publications, collaborations.
2.  **Lab Overview:** Brief text + Organizational Chart (Director -> Staff).
3.  **Events:** Displayed as Cards with Pagination.
4.  **Partners:** Grid of Institutional and Industrial partners.

### D. Team & Organization
*   **Structure:** Presentation of Lab -> Teams -> Members.
*   **Team Display:** Team Leader vs. Regular Members.
*   **Member Card:** Post, Grade, Photo, Link to Bio & Publications.
*   **Features:** Filtering/Sorting system.

### E. Research Project Catalogue
*   **Display:** Grid of "Cards" (Title, Manager, Members, Funding, Link).
*   **Categories:** AI, Security, Cloud, Networks, Embedded Systems, etc.
*   **Interactivity:**
    *   **AJAX Filtering:** Filter by Theme, Supervisor, or Status (Ongoing/Done/Submitted) without reloading the page.
    *   Details Page: Full description + Linked Publications.

### F. Authentication & User Dashboard
*   **Login:** Form for Teachers, PhDs, Students, Guests.
*   **Dashboard Features:**
    *   Update Profile (Photo, Research Domain, Bio).
    *   View "My Projects" and "My Publications".
    *   View "My Equipment Reservations".

### G. Publications & Documentation
*   **List:** Articles, Theses, Reports, Posters.
*   **Data:** Abstract, Authors, Date, DOI, PDF Download Link.
*   **Search:** Advanced search with dynamic sorting (Year, Author, Type).

### H. Equipment & Resources
*   **List:** Satus of resources (Rooms, Servers, PC, Robots).
*   **Status:** Free / Reserved / Maintenance.
*   **Action:** Reservation system (Booking a slot).

---

## 4. Part II: Back Office (Administration)
*Brief overview for structural planning.*

1.  **User Management:** CRUD Users, Assign Roles (Admin, Teacher, etc.), Suspend accounts.
2.  **Team Management:** Create teams, assign members/leaders.
3.  **Project Management:** Create/Close projects, **Generate PDF Reports**.
4.  **Resource Management:** Manage conflicts, schedule maintenance.
5.  **Publications:** **Validate** submitted papers before they go public.
6.  **Events:** Publish news/events.
7.  **Settings:** Change Logo, Theme Colors, **Backup/Restore DB**.

---

## 5. Client (Professor) "Soft" Requirements
*   **Design:** Minimalist, ergonomic, "No over-the-top effects."
*   **Code Quality:** Clean architecture, code reusability (Templates).
*   **Documentation:** UML Class Diagram (Domain) + SQL Diagram required.
*   **Validation:** Must be able to demonstrate and explain every line of code.