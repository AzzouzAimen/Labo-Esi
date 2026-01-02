<?php
/**
 * UserModel
 * Handles user authentication and profile management
 */
class UserModel extends Model {

    /**
     * Find user by username
     */
    public function findByUsername($username) {
        $stmt = $this->db->prepare("
            SELECT u.id_user, u.username, u.password, u.nom, u.prenom, u.email, u.photo, 
                   u.grade, u.poste, u.domaine_recherche, u.role_id,
                   r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.username = :username
        ");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Verify user credentials
     */
    public function authenticate($username, $password) {
        $user = $this->findByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        // Verify password using secure password hashing
        if (password_verify($password, $user['password'])) {
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }
        
        return false;
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare("
            SELECT u.id_user, u.username, u.nom, u.prenom, u.email, u.photo, 
                   u.grade, u.poste, u.domaine_recherche, u.role_id,
                   r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id_user = :id
        ");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET ";
        $fields = [];
        $params = [':id' => $userId];
        
        if (isset($data['photo'])) {
            $fields[] = "photo = :photo";
            $params[':photo'] = $data['photo'];
        }
        
        if (isset($data['domaine_recherche'])) {
            $fields[] = "domaine_recherche = :domaine";
            $params[':domaine'] = $data['domaine_recherche'];
        }
        
        if (isset($data['poste'])) {
            $fields[] = "poste = :poste";
            $params[':poste'] = $data['poste'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql .= implode(', ', $fields);
        $sql .= " WHERE id_user = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Change user password
     */
    public function changePassword($userId, $newPassword) {
        // Hash the password securely before storing
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password = :password 
            WHERE id_user = :id
        ");
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id' => $userId
        ]);
    }

    /**
     * Get all permissions for a user based on their role
     * 
     * @param int $userId The user ID
     * @return array Array of permission slugs
     */
    public function getPermissions($userId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.slug
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            INNER JOIN role_permissions rp ON r.id = rp.role_id
            INNER JOIN permissions p ON rp.permission_id = p.id
            WHERE u.id_user = :userId
        ");
        $stmt->execute([':userId' => $userId]);
        
        $permissions = [];
        while ($row = $stmt->fetch()) {
            $permissions[] = $row['slug'];
        }
        
        return $permissions;
    }

    /**
     * Check if a user has a specific permission
     * 
     * @param int $userId The user ID
     * @param string $permissionSlug The permission slug to check (e.g., 'edit_layout')
     * @return bool True if user has the permission, false otherwise
     */
    public function hasPermission($userId, $permissionSlug) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            INNER JOIN role_permissions rp ON r.id = rp.role_id
            INNER JOIN permissions p ON rp.permission_id = p.id
            WHERE u.id_user = :userId AND p.slug = :slug
        ");
        $stmt->execute([
            ':userId' => $userId,
            ':slug' => $permissionSlug
        ]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Get user's role information
     * 
     * @param int $userId The user ID
     * @return array|false Role data or false if not found
     */
    public function getUserRole($userId) {
        $stmt = $this->db->prepare("
            SELECT r.id, r.name, r.description
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE u.id_user = :userId
        ");
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetch();
    }
}
