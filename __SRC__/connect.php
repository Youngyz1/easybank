<?php
class DATABASE_CONNECT {
    public $connect;
    public function __construct()
    {
        $this->connect[0] = getenv("DB_HOST") ?: "localhost";
        $this->connect[1] = getenv("DB_USER") ?: "easybank";
        $this->connect[2] = getenv("DB_PASS") ?: "easybank";
        $this->connect[3] = getenv("DB_NAME") ?: "easybank";
    }
    public function get_connection()
    {
        $conn = mysqli_init();
        $conn->ssl_set(NULL, NULL, __DIR__ . "/rds-ca.pem", NULL, NULL);
        $conn->real_connect($this->connect[0], $this->connect[1], $this->connect[2], $this->connect[3], 3306, NULL, MYSQLI_CLIENT_SSL);
        if($conn->connect_error) die("Cannot connect: " . $conn->connect_error);
        return $conn;
    }
    public function __destruct()
    {
        $this->connect = null;
    }
}
?>