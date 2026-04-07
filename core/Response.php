<?php

declare(strict_types=1);

namespace DoubleE\Core;

class Response
{
    private string $body = '';
    private int $statusCode = 200;
    private array $headers = [];

    public function setBody(string $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function setStatusCode(int $code): static
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function redirect(string $url, int $code = 302): static
    {
        $this->statusCode = $code;
        $this->headers['Location'] = $url;
        return $this;
    }

    public function json(array $data, int $code = 200): static
    {
        $this->statusCode = $code;
        $this->headers['Content-Type'] = 'application/json';
        $this->body = json_encode($data, JSON_THROW_ON_ERROR);
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->body;
    }
}
