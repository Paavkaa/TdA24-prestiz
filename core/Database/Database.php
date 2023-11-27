<?php

namespace Core\Database;

use PDO;

class Database
{
    private readonly string $host;
    private readonly string $db;
    private readonly string $password;
    private readonly string $user;
    private readonly string $charset;
    private readonly string $dns;
    private PDO $pdo;

    public function __construct()
    {
        $this->host = getenv('MYSQL_PHP_HOST');
        $this->db = getenv('MYSQL_DATABASE');
        $this->password = getenv('MYSQL_PASSWORD');
        $this->user = getenv('MYSQL_USER');
        $this->charset = getenv('MYSQL_CHARSET');
        $this->dns = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        $this->pdo = new PDO($this->dns, 'root', 'root');
    }

    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        return $stmt->execute($params);
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollback()
    {
        return $this->pdo->rollback();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function guidv4()
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        try {
            $data = random_bytes(16);
            assert(strlen($data) == 16);

            // Set version to 0100
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            // Set bits 6-7 to 10
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

            // Output the 36 character UUID.
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        } catch (\Exception $e) {
            echo PHP_EOL . "Exception: " . $e->getMessage() . PHP_EOL;
        }
        return false;
    }
}