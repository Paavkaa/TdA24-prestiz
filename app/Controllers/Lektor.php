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
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['first_name']) || !isset($data['last_name'])) {
            http_response_code(400);
            echo json_encode([
                'code' => 400,
                'message' => 'Missing parameter uuid'
            ]);
            return;
        }
        $uuid = $this->lektorModel->createLector($data);

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

    public function put(stdClass $data): void
    {
        $postData = json_decode(file_get_contents('php://input'), true);

        if (!isset($data->uuid) || is_null($postData['first_name']) || is_null($postData['last_name'])) {
            http_response_code(404);
            echo json_encode([
                'code' => 404,
                'message' => 'Missing parameter uuid'
            ]);
            return;
        }
        header('Content-Type: application/json');
        $response = $this->lektorModel->updateLecturer($data->uuid, $postData);
        if ($response === false) {
            http_response_code(400);
            echo json_encode([
                'code' => 404,
                'message' => 'User not found'
            ]);
        } else {
            http_response_code(200);
            echo json_encode($this->lektorModel->getById($data->uuid));
        }
    }

    public function delete(stdClass $data): void
    {
        if (!isset($data->uuid)) {
            http_response_code(400);
            echo json_encode([
                'code' => 400,
                'message' => 'Missing parameter uuid'
            ]);
            return;
        }
        header('Content-Type: application/json');
        $response = $this->lektorModel->lecturerDelete($data->uuid);
        if ($response === false) {
            http_response_code(404);
            echo json_encode([
                'code' => 404,
                'message' => 'User not found'
            ]);
        } else {
            http_response_code(204);
        }

    }

    public function viewOne(stdClass $data): void
    {
        if (!isset($data->uuid)) {
            http_response_code(400);
            echo json_encode([
                'code' => 400,
                'message' => 'Missing parameter uuid'
            ]);
            return;
        }
        $uuid = $data->uuid;
        $lektor = $this->lektorModel->getById($uuid);
        $view = new View('lektor/lektor.php');
        $view->render($lektor);

    }

    public function index(): void
    {
        $view = new View('lektor/index.php');
        $view->render([]);
    }
}