<?php

namespace App\Controllers;

use Core\Controller;
use \Core\Database\Database;
use \App\Views\View;

class Lektor extends Controller
{
    public function __construct(
        private readonly Database $database,
    )
    {
    }

    public function get(): void
    {
        echo 'Hello from Lektor controller!';
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare('SELECT * FROM test');
        echo "test";
        $stmt->execute();
        $lektor = $stmt->fetchAll();
        echo json_encode($lektor);
    }

    public function index(): void
    {
        $view = new View('lektor/index.php');
        $view->render([]);
    }
}