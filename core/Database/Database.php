<?php

namespace Core\Database;

use PDO;
class Database
{
    private PDO $conn;
    private readonly string $host;
    private readonly string $db;
    private readonly string $password;
    private readonly string $user;
    private readonly string $charset;
    private readonly string $dns;
    public function __construct() {
        $this->host = getenv('MYSQL_PHP_HOST');
        $this->db = getenv('MYSQL_DATABASE');
        $this->password = getenv('MYSQL_PASSWORD');
        $this->user = getenv('MYSQL_USER');
        $this->charset = getenv('MYSQL_CHARSET');
        $this->dns = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        $this->conn = new PDO($this->dns, 'root', 'root');
    }
    public function getConnection(): PDO {
        return $this->conn;
    }
}