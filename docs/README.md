# TDW Laboratory Management System - Part I (Front Office)

## 📋 Project Overview
This is the **Front Office** implementation of the University Laboratory Management System, developed according to strict MVC architecture and technical specifications for TDW-2CSSIL module.

## ✅ Implemented Features (Part I)

### Phase 1-2: Core Infrastructure ✓
- **MVC Architecture**: Strict separation of Model, View, Controller
- **Singleton Database**: PDO-based connection with error handling
- **Router**: Front Controller pattern with URL parsing
- **Base Classes**: Controller and View abstract classes
- **View as Classes**: All views are objects (not just included files)
- **Internationalization**: All text stored in language files (`lang/fr.php`)

### Phase 3: Homepage ✓
- **Dynamic Slideshow**: 
  - Fetches latest 5 news/events from database
  - Auto-scrolls every 5 seconds (jQuery)
  - Manual navigation controls
  - Clickable links to event details
- **Lab Overview**: Brief presentation section
- **Upcoming Events**: Card-based display with event details
- **Partners Section**: Display of institutional partners

### Phase 4: Project Catalog with AJAX ✓
- **Project Listing**: Grid display of all research projects
- **AJAX Filtering**: Real-time filtering by:
  - Domain (IA, Sécurité, Cloud, Réseaux, etc.)
  - Status (En cours, Terminé, Soumis)
  - **No page reload** using jQuery
- **Project Detail Page**:
  - Full description
  - Project manager information
  - Team members with photos
  - Linked publications
  - Partner organizations

### Phase 5: Team Organization ✓
- **Team Listing**: Display all laboratory teams
- **Hierarchical Display**:
  - Team Leader highlighted with special styling
  - Regular members in grid layout
- **Member Cards**: Photo, grade, post, research domain
- **Member Profile Page**:
  - Full biography
  - List of projects
  - List of publications

### Phase 6: Authentication ✓
- **Login System**:
  - Form validation
  - Session management
  - Required test accounts (admin/admin, user/user)
- **User Dashboard**:
  - Profile overview
  - My Projects section
  - My Publications section
- **Profile Management**:
  - Update research domain
  - Update post/position
  - Photo upload (future enhancement)

## 🗂️ Project Structure

```
/proj
├── /app
│   ├── /Config
│   │   └── config.php              # Database & app configuration
│   ├── /Controllers
│   │   ├── HomeController.php      # Homepage logic
│   │   ├── ProjectController.php   # Projects & filtering
│   │   ├── TeamController.php      # Teams & members
│   │   ├── AuthController.php      # Login/logout
│   │   └── DashboardController.php # User dashboard
│   ├── /Models
│   │   ├── NewsModel.php           # Events/news data
│   │   ├── ProjectModel.php        # Project data
│   │   ├── TeamModel.php           # Team/member data
│   │   └── UserModel.php           # User authentication
│   └── /Views
│       ├── /Classes                # View Objects
│       │   ├── HomeView.php
│       │   ├── ProjectView.php
│       │   ├── ProjectDetailView.php
│       │   ├── TeamView.php
│       │   ├── MemberProfileView.php
│       │   ├── LoginView.php
│       │   ├── DashboardView.php
│       │   └── LayoutView.php
│       └── /Templates              # HTML Templates
│           ├── layout.phtml        # Main layout (header/nav/footer)
│           ├── home.phtml
│           ├── project_list.phtml
│           ├── project_detail.phtml
│           ├── team_list.phtml
│           ├── member_profile.phtml
│           ├── login.phtml
│           └── dashboard.phtml
├── /core                           # Framework Core
│   ├── Database.php                # Singleton PDO
│   ├── Router.php                  # URL routing
│   ├── Controller.php              # Base controller
│   └── View.php                    # Base view
├── /public                         # Web Root
│   ├── index.php                   # Entry point
│   └── /assets
│       ├── /css
│       │   └── style.css           # Main stylesheet
│       ├── /js
│       │   └── script.js           # jQuery logic
│       ├── /img                    # Static images
│       └── /uploads                # User uploads
└── /lang
    └── fr.php                      # French language strings
```

## 🚀 Installation & Setup

### 1. Prerequisites
- **WAMP** (Windows Apache MySQL PHP) or similar environment
- **PHP** 7.4+ with PDO extension
- **MySQL** 5.7+ or MariaDB
- Modern web browser with JavaScript enabled

### 2. Database Setup
1. Create database named `TDW` in phpMyAdmin
2. Run the SQL script from `db-script.md` to create tables and seed data
3. Verify the required user accounts exist:
   - Username: `admin` / Password: `admin`
   - Username: `user` / Password: `user`

### 3. Configuration
1. Open `app/Config/config.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'TDW');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Default WAMP password
   ```
3. Verify `BASE_URL` matches your setup:
   ```php
   define('BASE_URL', 'http://localhost/proj/public/');
   ```

### 4. File Permissions
Create the uploads directory if it doesn't exist:
```bash
mkdir public/uploads
mkdir public/uploads/profiles
```

### 5. Access the Application
1. Start WAMP services
2. Navigate to: `http://localhost/proj/public/`
3. Test login with: `admin` / `admin`

## 🎯 Technical Constraints Met

| Constraint | Status | Implementation |
|------------|--------|----------------|
| **MVC Architecture** | ✅ | Strict separation in `/app` directory |
| **Class-based Views** | ✅ | View objects in `/app/Views/Classes` |
| **Text as Variables** | ✅ | All strings in `/lang/fr.php` |
| **No External Libraries** | ✅ | Only jQuery (allowed) used |
| **Database Name: TDW** | ✅ | Configured in `config.php` |
| **Required Credentials** | ✅ | admin/admin and user/user in DB |
| **Min 3 Rows/Table** | ✅ | Seed data in SQL script |
| **AJAX Filtering** | ✅ | Project catalog with no page reload |
| **Slideshow (5sec)** | ✅ | jQuery auto-scroll implemented |
| **Team Hierarchy** | ✅ | Leader vs Members distinction |

## 🔧 Key Features & Technologies

### Backend
- **Pure PHP** (no frameworks)
- **PDO** for secure database operations
- **Singleton Pattern** for database connection
- **Front Controller Pattern** for routing
- **Prepared Statements** to prevent SQL injection

### Frontend
- **Responsive CSS** (Flexbox & Grid)
- **jQuery 3.6** for DOM manipulation and AJAX
- **Minimalist Design** (as per specifications)
- **Mobile-friendly** layout

### Security
- Session-based authentication
- HTML escaping for XSS prevention
- Prepared statements for SQL injection prevention
- File upload validation (for future enhancements)

## 📝 Usage Examples

### Homepage
```
http://localhost/proj/public/index.php?controller=Home&action=index
```

### Project Catalog
```
http://localhost/proj/public/index.php?controller=Project&action=index
```

### Team Organization
```
http://localhost/proj/public/index.php?controller=Team&action=index
```

### Login
```
http://localhost/proj/public/index.php?controller=Auth&action=login
```

### Dashboard (requires login)
```
http://localhost/proj/public/index.php?controller=Dashboard&action=index
```

## 🎨 Design Philosophy
- **Minimalist**: Clean, professional interface
- **Ergonomic**: Intuitive navigation
- **Responsive**: Works on desktop, tablet, mobile
- **Fast**: Optimized database queries
- **Accessible**: Semantic HTML structure

## 🔜 Next Steps (Part II - Back Office)
The following features are planned for Part II:
1. **Admin Dashboard**: Full CRUD operations
2. **User Management**: Create/edit/suspend accounts
3. **Team Management**: Assign members/leaders
4. **Project Management**: Create/close projects, PDF reports
5. **Resource Management**: Equipment scheduling
6. **Publication Validation**: Review before public display
7. **System Settings**: Logo, colors, database backup

## 📚 Documentation
- **Specs**: See `specs.md` for full project specifications
- **Plan**: See `plan-part1.md` for implementation guide
- **Database**: See `db-script.md` for schema and seed data

## ⚠️ Important Notes
1. **Passwords**: Currently stored in plain text (for development only). In production, use `password_hash()` and `password_verify()`
2. **File Uploads**: Basic structure in place, needs enhancement for production
3. **Error Handling**: Basic implementation, can be enhanced
4. **Validation**: Client-side only, add server-side validation for production

## 👨‍💻 Development Notes
- All code follows PSR-1/PSR-2 coding standards
- Comments in French (as per academic context)
- Clear separation of concerns (MVC)
- Reusable components (View templates)
- Scalable architecture for Part II additions

## ✨ Highlights
- ✅ **100% MVC compliant**
- ✅ **All views are classes** (not just includes)
- ✅ **Real AJAX filtering** (no page reload)
- ✅ **Automatic slideshow** with manual controls
- ✅ **Clean, professional design**
- ✅ **Ready for Part II** expansion

---

**Project Status**: Part I Complete ✅  
**Next Phase**: Back Office (Part II)  
**Last Updated**: December 2025
