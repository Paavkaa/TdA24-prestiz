<?php

namespace Core;

use Core\Http\Request;

abstract class Controller
{
    public function get(Request $request): void
    {
        throw new \Exception('Not implemented');
    }

    public function post(Request $request): void
    {
        throw new \Exception('Not implemented');
    }

    public function update(): void
    {
        throw new \Exception('Not implemented');
    }

    public function index(): void
    {
        throw new \Exception('Not implemented');
    }
}