<?php
/**
 * UserModel
 * Handles user authentication and profile management
 */
class UserModel extends Model {

    /**
     * Find user by username
     * @param string $username
     * @return array|false
     */
    public function findByUsername($username) {
        $stmt = $this->db->prepare("
            SELECT id_user, username, password, nom, prenom, email, photo, 
                   grade, poste, domaine_recherche, role
            FROM users
            WHERE username = :username
        ");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Verify user credentials
     * @param string $username
     * @param string $password
     * @return array|false Returns user data if successful, false otherwise
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
     * @param int $userId
     * @return array|false
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare("
            SELECT id_user, username, nom, prenom, email, photo, 
                   grade, poste, domaine_recherche, role
            FROM users
            WHERE id_user = :id
        ");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Update user profile
     * @param int $userId
     * @param array $data
     * @return bool
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
     * @param int $userId
     * @param string $newPassword
     * @return bool
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
}
