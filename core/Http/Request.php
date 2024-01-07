<?php

namespace Core\Http;

use \stdClass;

class Request
{

    public function __construct(private readonly stdClass $urlParams = new stdClass())
    {
    }

    public function getUrlParams(): stdClass
    {
        return $this->urlParams;
    }

    public function getQueryParams(): stdClass
    {
        return (object)$_GET;
    }

    public function getBody(): stdClass
    {
        if ($this->isJsonRequest()) {
            return (object)json_decode(file_get_contents('php://input'), true) ?? (object)[];
        }
        return (object)$_POST;
    }

    public function isJsonRequest(): bool
    {
        return 'application/json' === $this->getContentType();
    }

    public function getContentType(): ?string
    {
        return $_SERVER['CONTENT_TYPE'] ?? null;
    }
}