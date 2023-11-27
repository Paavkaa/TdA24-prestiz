<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Lektor as LektorModel;
use \App\Views\View;
use stdClass;

class Lektor extends Controller
{
    public function __construct(
        private readonly LektorModel $lektorModel,
    )
    {
    }

    public function get(): void
    {
        header('Content-Type: application/json');
        $lektor = $this->lektorModel->getAll();
        echo json_encode($lektor);
    }

    public function getOne(stdClass $params): void
    {
        if (!isset($params->uuid)) {
            http_response_code(400);
            echo json_encode([
                'code' => 400,
                'message' => 'Missing parameter uuid'
            ]);
            return;
        }
        $uuid = $params->uuid;
        header('Content-Type: application/json');
        $lektor = $this->lektorModel->getById($uuid);
        if ($lektor === false) {
            http_response_code(404);
            echo json_encode([
                'code' => 404,
                'message' => 'User not found'
            ]);
            return;
        }
        echo json_encode($lektor);
    }

    public function post(): void
    {
        header('Content-Type: application/json');
        $uuid = $this->lektorModel->createLector(json_decode(file_get_contents('php://input'), true));
        if ($uuid === false) {
            http_response_code(400);
            echo json_encode([
                'code' => 400,
                'message' => 'Missing parameter uuid'
            ]);
        } else {
            http_response_code(200);
            echo json_encode($this->lektorModel->getById($uuid));
        }
    }

    public function index(): void
    {
        $view = new View('lektor/index.php');
        $view->render([]);
    }
}