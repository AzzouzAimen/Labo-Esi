-- ============================================================
-- 1. Users Table
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    photo VARCHAR(255),
    grade VARCHAR(100) NOT NULL,
    poste VARCHAR(100),
    domaine_recherche VARCHAR(255),
    role ENUM('admin','enseignant-chercheur','doctorant','etudiant','invite') DEFAULT 'enseignant-chercheur',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 2. Teams Table
-- ============================================================
CREATE TABLE IF NOT EXISTS teams (
    id_team INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) UNIQUE NOT NULL,
    description TEXT,
    chef_id INT,
    FOREIGN KEY (chef_id) REFERENCES users(id_user) ON DELETE SET NULL
);

-- ============================================================
-- 3. Team Members (Association)
-- ============================================================
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    usr_id INT NOT NULL,
    role_dans_equipe VARCHAR(100),
    FOREIGN KEY (team_id) REFERENCES teams(id_team) ON DELETE CASCADE,
    FOREIGN KEY (usr_id) REFERENCES users(id_user) ON DELETE CASCADE
);

-- ============================================================
-- 4. Projects (Required for Catalogue)
-- ============================================================
CREATE TABLE IF NOT EXISTS projects (
    id_project INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    responsable_id INT,
    domaine ENUM('IA', 'Sécurité', 'Cloud', 'Réseaux', 'Systèmes Embarqués', 'Web') NOT NULL,
    statut ENUM('en cours', 'terminé', 'soumis') DEFAULT 'en cours',
    type_financement VARCHAR(100),
    date_debut DATE,
    image_url VARCHAR(255),
    FOREIGN KEY (responsable_id) REFERENCES users(id_user) ON DELETE SET NULL
);

-- ============================================================
-- 5. Partners (Required for Home & Projects)
-- ============================================================
CREATE TABLE IF NOT EXISTS partners (
    id_partner INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    logo_url VARCHAR(255),
    type ENUM('université', 'entreprise', 'organisme') NOT NULL,
    site_web VARCHAR(255)
);

-- ============================================================
-- 6. Events / News (Required for Slideshow)
-- ============================================================
CREATE TABLE IF NOT EXISTS events (
    id_event INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    date_event DATETIME,
    type ENUM('conference', 'atelier', 'soutenance', 'actualite') NOT NULL,
    image_url VARCHAR(255),
    lieu VARCHAR(150)
);

-- ============================================================
-- 7. Publications (Required for Profile & Project)
-- ============================================================
CREATE TABLE IF NOT EXISTS publications (
    id_pub INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    resume TEXT,
    date_publication DATE,
    lien_pdf VARCHAR(255),
    doi VARCHAR(100),
    type ENUM('article', 'these', 'rapport', 'poster', 'communication') NOT NULL,
    project_id INT,
    FOREIGN KEY (project_id) REFERENCES projects(id_project) ON DELETE SET NULL
);

-- ============================================================
-- 8. Project_Members (Many-to-Many)
-- ============================================================
CREATE TABLE IF NOT EXISTS project_members (
    id_project INT,
    id_user INT,
    role_dans_projet VARCHAR(100),
    PRIMARY KEY (id_project, id_user),
    FOREIGN KEY (id_project) REFERENCES projects(id_project) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- ============================================================
-- 9. Project Partners (Many-to-Many)
-- Links a Project to multiple Partners (Industrial/Academic)
-- ============================================================
CREATE TABLE IF NOT EXISTS project_partners (
    id_project INT,
    id_partner INT,
    PRIMARY KEY (id_project, id_partner),
    FOREIGN KEY (id_project) REFERENCES projects(id_project) ON DELETE CASCADE,
    FOREIGN KEY (id_partner) REFERENCES partners(id_partner) ON DELETE CASCADE
);

-- ============================================================
-- 10. Publication Authors (Many-to-Many)
-- Links a Publication to multiple Users (Authors)
-- ============================================================
CREATE TABLE IF NOT EXISTS publication_authors (
    id_pub INT,
    id_user INT,
    PRIMARY KEY (id_pub, id_user),
    FOREIGN KEY (id_pub) REFERENCES publications(id_pub) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- ============================================================
-- 11. Equipment (Part I - Section 7 & Part II - Section 4)
-- ============================================================
CREATE TABLE IF NOT EXISTS equipment (
    id_equip INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    reference VARCHAR(50) UNIQUE, -- Internal ID (e.g., PC-001)
    categorie ENUM('Salle', 'Serveur', 'PC', 'Robot', 'Imprimante', 'Capteur', 'Autre') NOT NULL,
    description TEXT,
    etat ENUM('libre', 'réservé', 'maintenance') DEFAULT 'libre',
    image_url VARCHAR(255),
    date_acquisition DATE
);

-- ============================================================
-- 12. Reservations (Part I - Section 7)
-- ============================================================
CREATE TABLE IF NOT EXISTS reservations (
    id_res INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    equip_id INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    motif TEXT,
    -- Prevent double booking via application logic, 
    -- but here we link relationships
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (equip_id) REFERENCES equipment(id_equip) ON DELETE CASCADE
);

-- ============================================================
-- 13. Maintenances (Part II - Section 4)
-- Allows Admin to schedule downtime for equipment
-- ============================================================
CREATE TABLE IF NOT EXISTS maintenances (
    id_maint INT AUTO_INCREMENT PRIMARY KEY,
    equip_id INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    description TEXT, -- Reason for maintenance
    technicien VARCHAR(100), -- Name of external or internal tech
    FOREIGN KEY (equip_id) REFERENCES equipment(id_equip) ON DELETE CASCADE
);

-- ============================================================
-- 14. Event Registrations (Part II - Section 6)
-- Allows users/visitors to register for conferences/seminars
-- ============================================================
CREATE TABLE IF NOT EXISTS event_registrations (
    id_reg INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT, -- If internal member
    visiteur_nom VARCHAR(100), -- If external visitor
    visiteur_email VARCHAR(150), -- If external visitor
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id_event) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE
);

-- ============================================================
-- 15. Settings (Part II - Section 7)
-- Stores dynamic logo, theme colors, etc.
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY, -- e.g., 'site_logo', 'theme_color'
    setting_value TEXT
);

-- ============================================================
-- SEEDING (Insert required default settings)
-- ============================================================
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('site_name', 'Laboratoire TDW-2CSSIL'),
('theme_color', '#3498db'),
('contact_email', 'contact@labo-tdw.dz');

-- ============================================================
-- DATA SEEDING (Minimum 3 rows per table)
-- ============================================================

-- 1. Users (Required: admin/admin and user/user + additional members)
-- NOTE: Passwords are now hashed using password_hash() with PASSWORD_DEFAULT (bcrypt)
INSERT INTO users (username, password, nom, prenom, email, grade, role) VALUES 
('admin', '$2y$10$52TrqoWvR5UqRu8/bTWBwut3GMOlhUsdS5ivfFxlsEKhyuXvUTT0K', 'Directeur', 'Laboratoire', 'admin@labo-tdw.dz', 'Professeur', 'admin'),
('user', '$2y$10$mAxfZ2rTovYkdsxySzdY1uvvKT9UQyQXn8ZGlL12Om6ZIbO4TlEcy', 'Chercheur', 'Membre', 'user@labo-tdw.dz', 'Maître de Conférences', 'enseignant-chercheur'),
('prof_a', '$2y$10$jA84KEsPtkbbqKQYF76FzuU0YslwNrgUlk5mIxevm.hjSZm0Km0I6', 'Benali', 'Mohamed', 'm.benali@esi.dz', 'Professeur', 'enseignant-chercheur'),
('phd_b', '$2y$10$jA84KEsPtkbbqKQYF76FzuU0YslwNrgUlk5mIxevm.hjSZm0Km0I6', 'Amrani', 'Sarah', 's.amrani@esi.dz', 'Doctorante', 'doctorant'),
('stud_c', '$2y$10$jA84KEsPtkbbqKQYF76FzuU0YslwNrgUlk5mIxevm.hjSZm0Km0I6', 'Kader', 'Yacine', 'y.kader@esi.dz', 'Etudiant', 'etudiant');
-- 2. Teams
INSERT INTO teams (nom, description, chef_id) VALUES 
('Equipe IA', 'Intelligence Artificielle et Data Mining', 3), -- ID 3 is Prof Benali
('Equipe Securité', 'Cybersécurité et Cryptographie', 1), -- ID 1 is Admin
('Equipe IoT', 'Internet des Objets et Systèmes Embarqués', 3);

-- 3. Projects
INSERT INTO projects (titre, domaine, statut, responsable_id) VALUES 
('Smart City Algiers', 'IA', 'en cours', 3),
('Secure Cloud Protocol', 'Sécurité', 'soumis', 1),
('AgriTech Drone', 'Systèmes Embarqués', 'terminé', 3);

-- 4. Equipment
INSERT INTO equipment (nom, categorie, etat) VALUES 
('Serveur Dell PowerEdge', 'Serveur', 'libre'),
('Salle de Réunion A', 'Salle', 'réservé'),
('Imprimante 3D', 'Autre', 'libre');

-- 5. Partners
INSERT INTO partners (nom, type) VALUES 
('Sonatrach', 'entreprise'),
('USTHB', 'université'),
('CERIST', 'organisme');

-- 6. Events / News
INSERT INTO events (titre, type, date_event, description) VALUES 
('Conférence AI 2025', 'conference', '2025-06-15 09:00:00', 'Grande conférence sur l\'IA.'),
('Soutenance de Thèse', 'soutenance', '2025-05-20 14:00:00', 'Soutenance de M. Amrani.'),
('Nouvelle publication', 'actualite', NOW(), 'Le laboratoire a publié 3 articles IEEE.');