<?php

namespace App\Controllers;

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