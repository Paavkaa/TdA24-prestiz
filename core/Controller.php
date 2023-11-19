<?php

namespace Core;

abstract class Controller
{
    public function get(): void
    {
        throw new \Exception('Not implemented');
    }
    public function post(): void
    {
        throw new \Exception('Not implemented');
    }
    public function update(): void
    {
        throw new \Exception('Not implemented');
    }
    public function delete(): void
    {
        throw new \Exception('Not implemented');
    }
    public function index(): void
    {
        throw new \Exception('Not implemented');
    }
}