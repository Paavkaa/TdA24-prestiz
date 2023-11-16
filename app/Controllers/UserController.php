<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Views\View;

class UserController {
    public function index(): void
    {
        $userModel = new UserModel();
        $users = $userModel->getAllUsers();

        // Vytvoření instance pohledu a předání dat
        $view = new View('user/index.php');
        $view->render(['users' => $users]);
    }

    // Další akce pro vytváření, aktualizaci a mazání uživatelů...
}
