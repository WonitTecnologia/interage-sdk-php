<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Http;

/**
 * Resposta HTTP devolvida pelo {@see Transport}.
 */
final class Response
{
    /** @var array<string, string> */
    public readonly array $headers;

    /**
     * @param array<string, string> $headers Nomes em qualquer grafia; normalizados para minúsculas.
     */
    public function __construct(
        public readonly int $statusCode,
        array $headers,
        public readonly string $body,
    ) {
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }
}
