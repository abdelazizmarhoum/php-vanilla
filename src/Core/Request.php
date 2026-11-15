<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed>|string $body
     * @param array<string, string> $headers
     * @param array<string, string> $params
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array|string $body = [],
        public readonly array $headers = [],
        private array $params = [],
        private ?array $currentUser = null,
        public ?array $workspace = null,
        public ?array $membership = null,
    ) {
    }

    public function param(string $name): ?string
    {
        return $this->params[$name] ?? null;
    }

    public function user(): ?array
    {
        return $this->currentUser;
    }

    public function workspace(): ?array
    {
        return $this->workspace;
    }

    public function membership(): ?array
    {
        return $this->membership;
    }

    /** @param array<string, string> $params */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function setUser(?array $user): void
    {
        $this->currentUser = $user;
    }

    public function setWorkspace(?array $workspace): void
    {
        $this->workspace = $workspace;
    }

    public function setMembership(?array $membership): void
    {
        $this->membership = $membership;
    }
}