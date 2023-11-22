<?php

namespace App\Controllers;

use Core\Controller;

class Api extends Controller
{
    public function get(): void
    {
        $data = [
            'secret'=>'The cake is a lie',
        ];
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}