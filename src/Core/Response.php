<?php
declare(strict_types=1);

namespace App\Core;

use JsonException;

final class Response
{
    private int $statusCode = 200;

    private string $body = '';

    /**
     * @var array<string, string>
     */
    private array $headers = [];

    private bool $sent = false;

    public function html(string $html, int $status = 200): self
    {
        $this->body = $html;
        $this->statusCode = $status;
        $this->setHeader('Content-Type', 'text/html; charset=UTF-8');

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function json(mixed $data, int $status = 200): self
    {
        $this->body = json_encode($data, JSON_THROW_ON_ERROR);
        $this->statusCode = $status;
        $this->setHeader('Content-Type', 'application/json; charset=UTF-8');

        return $this;
    }

    public function redirect(string $url, int $status = 302): self
    {
        $this->body = '';
        $this->statusCode = $status;
        $this->setHeader('Location', $url);

        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function status(int $code): self
    {
        $this->statusCode = $code;

        return $this;
    }

    public function send(): void
    {
        if ($this->sent) {
            return;
        }

        $this->sent = true;
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->body;
    }
}
