<?php
/**
 * Secure Database Helper Class
 * Uses prepared statements to prevent SQL injection
 */

class SECURE_DB {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Execute a SELECT query with parameters
     * @param string $sql SQL with ? placeholders
     * @param array $params Array of parameters
     * @return mysqli_result|false
     */
    public function select($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    
    /**
     * Execute an INSERT/UPDATE/DELETE query with parameters
     * @param string $sql SQL with ? placeholders
     * @param array $params Array of parameters
     * @return bool
     */
    public function execute($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    /**
     * Get a single row as associative array
     * @param string $sql SQL with ? placeholders
     * @param array $params Array of parameters
     * @return array|null
     */
    public function fetchRow($sql, $params = []) {
        $result = $this->select($sql, $params);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    /**
     * Get a single value from a query
     * @param string $sql SQL with ? placeholders
     * @param array $params Array of parameters
     * @return mixed
     */
    public function fetchValue($sql, $params = []) {
        $result = $this->select($sql, $params);
        if ($result && $row = $result->fetch_assoc()) {
            return reset($row);
        }
        return null;
    }
    
    /**
     * Get number of affected rows
     */
    public function affectedRows() {
        return $this->conn->affected_rows;
    }
}
?>