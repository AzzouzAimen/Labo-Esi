<?php
// core/Model.php

abstract class Model {
    protected $db;

    public function __construct() {
        // Automatically connect to DB when any model is created
        $this->db = Database::getInstance();
    }

    /**
     * Generic method to get all rows from a table
     * Useful for simple dropdowns like 'categories' or 'cities'
     */
    public function findAll($table) {
        $stmt = $this->db->query("SELECT * FROM $table");
        return $stmt->fetchAll();
    }

    /**
     * Generic method to find by ID
     */
    public function findById($table, $pkName, $id) {
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE $pkName = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
