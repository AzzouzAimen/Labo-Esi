# Quick Start Guide - TDW Laboratory System

## 🚀 Getting Started in 5 Minutes

### Step 1: Database Setup
1. Open **phpMyAdmin** (http://localhost/phpmyadmin)
2. Create a new database named **`TDW`**
3. Select the database and go to **SQL** tab
4. Copy all content from **`db-script.md`** and execute it
5. Verify that tables are created and data is seeded

### Step 2: Verify Installation
1. Check that WAMP is running (icon should be green)
2. Open your browser
3. Go to: **http://localhost/proj/public/**
4. You should see the homepage with slideshow

### Step 3: Test Features

#### Test the Homepage
- ✅ Slideshow should auto-advance every 5 seconds
- ✅ Click navigation to manually control slides
- ✅ See upcoming events and lab overview

#### Test Project Catalog
1. Click **"Projets"** in navigation
2. Try filtering by:
   - Domain (dropdown)
   - Status (dropdown)
3. Notice the page doesn't reload (AJAX)
4. Click **"Voir les détails"** on any project

#### Test Team Organization
1. Click **"Équipes"** in navigation
2. See teams with their leaders highlighted
3. Click **"Voir les détails"** on any member
4. View their profile, projects, and publications

#### Test Authentication
1. Click **"Connexion"** in navigation
2. Use credentials:
   - **Admin**: `admin` / `admin`
   - **User**: `user` / `user`
3. Access your dashboard
4. View your projects and publications
5. Click **"Déconnexion"** to logout

## 🔧 Troubleshooting

### Database Connection Error
**Problem**: "Database Connection Failed"
**Solution**:
1. Open `app/Config/config.php`
2. Verify credentials:
   ```php
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Empty for default WAMP
   ```
3. Check WAMP MySQL service is running

### Page Not Found / 404 Error
**Problem**: All pages show 404
**Solution**:
1. Verify BASE_URL in `app/Config/config.php`:
   ```php
   define('BASE_URL', 'http://localhost/proj/public/');
   ```
2. Make sure you're accessing through `/public/` directory

### Slideshow Not Working
**Problem**: Images don't auto-scroll
**Solution**:
1. Check browser console for JavaScript errors (F12)
2. Verify jQuery is loading: `https://code.jquery.com/jquery-3.6.0.min.js`
3. Clear browser cache (Ctrl+Shift+Delete)

### AJAX Filter Not Working
**Problem**: Dropdown filters don't update projects
**Solution**:
1. Open browser console (F12)
2. Check for AJAX errors
3. Verify database has projects with different domains/statuses
4. Check network tab to see if request is being sent

### Missing Images
**Problem**: Broken image icons
**Solution**:
1. This is normal - placeholder images not included
2. Application has fallback behavior:
   - Logos show text
   - User photos show colored initials
3. Add your own images to `/public/assets/img/`

### Login Doesn't Work
**Problem**: "Identifiants incorrects"
**Solution**:
1. Verify seed data was inserted:
   ```sql
   SELECT * FROM users WHERE username='admin';
   ```
2. Make sure you're using exact credentials:
   - Username: `admin`
   - Password: `admin`
3. Check for typos (case-sensitive)

## 📍 Key URLs

| Page | URL |
|------|-----|
| Homepage | `http://localhost/proj/public/` |
| Projects | `http://localhost/proj/public/index.php?controller=Project&action=index` |
| Teams | `http://localhost/proj/public/index.php?controller=Team&action=index` |
| Login | `http://localhost/proj/public/index.php?controller=Auth&action=login` |
| Dashboard | `http://localhost/proj/public/index.php?controller=Dashboard&action=index` |

## 🎯 Test Checklist

Use this checklist to verify all features are working:

- [ ] Homepage loads with slideshow
- [ ] Slideshow auto-advances every 5 seconds
- [ ] Manual slideshow controls work
- [ ] Navigation menu is visible and clickable
- [ ] Project catalog displays projects
- [ ] Domain filter works (AJAX - no reload)
- [ ] Status filter works (AJAX - no reload)
- [ ] Project detail page shows full information
- [ ] Team page shows all teams
- [ ] Team leaders are highlighted
- [ ] Member profile pages work
- [ ] Login form appears
- [ ] Can login with admin/admin
- [ ] Dashboard shows user info
- [ ] Dashboard shows projects
- [ ] Dashboard shows publications
- [ ] Logout works and redirects to home
- [ ] Footer displays correctly
- [ ] All links in navigation work

## 💡 Next Steps

Once everything is working:

1. **Explore the code**:
   - Check `/app/Controllers` for business logic
   - Check `/app/Models` for database queries
   - Check `/app/Views` for presentation logic

2. **Customize**:
   - Add your own images to `/public/assets/img/`
   - Modify colors in `/public/assets/css/style.css`
   - Add more seed data to database

3. **Prepare for Part II**:
   - Review specifications for Back Office features
   - Plan admin dashboard functionality
   - Think about CRUD operations needed

## 📞 Support

If you encounter issues not covered here:

1. Check browser console for JavaScript errors (F12)
2. Check Apache error logs in WAMP
3. Review the code comments for guidance
4. Refer to `README.md` for full documentation

## ✨ Success!

If all items in the test checklist pass, you have successfully set up Part I of the Laboratory Management System! 🎉

**Status**: Ready for demonstration and Part II development.
