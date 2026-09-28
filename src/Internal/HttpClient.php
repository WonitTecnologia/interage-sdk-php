<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Internal;

use WonitTecnologia\Interage\Exception\ApiException;
use WonitTecnologia\Interage\Exception\UnexpectedResponseException;
use WonitTecnologia\Interage\Http\Request;
use WonitTecnologia\Interage\Http\Transport;

/**
 * Transporte interno compartilhado por todos os domínios. Centraliza
 * autenticação, montagem das requisições, unwrap do envelope
 * {code,status,message,data} e tratamento de erros HTTP.
 *
 * @internal Não faz parte da API pública do SDK.
 */
final class HttpClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
        private readonly Transport $transport,
        private readonly float $timeout,
        private readonly string $userAgent,
    ) {
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<mixed>|null O campo `data` do envelope.
     */
    public function get(string $path, array $query = []): ?array
    {
        return $this->send('GET', $path, $query, null, null);
    }

    /**
     * @param array<mixed>|null          $body  Corpo JSON (null = sem corpo).
     * @param array<string, scalar|null> $query
     * @return array<mixed>|null
     */
    public function post(string $path, ?array $body = null, array $query = []): ?array
    {
        return $this->send('POST', $path, $query, ...$this->json($body));
    }

    /**
     * @param array<mixed>|null $body
     * @return array<mixed>|null
     */
    public function patch(string $path, ?array $body = null): ?array
    {
        return $this->send('PATCH', $path, [], ...$this->json($body));
    }

    /**
     * @return array<mixed>|null
     */
    public function delete(string $path): ?array
    {
        return $this->send('DELETE', $path, [], null, null);
    }

    /**
     * POST multipart/form-data com campos de texto e um arquivo.
     *
     * @param array<string, string> $fields
     * @return array<mixed>|null
     */
    public function postMultipart(
        string $path,
        array $fields,
        string $fileField,
        string $fileName,
        string $fileContent,
        string $fileContentType,
    ): ?array {
        $boundary = 'interage-' . bin2hex(random_bytes(16));
        $body = '';
        foreach ($fields as $name => $value) {
            $body .= '--' . $boundary . "\r\n"
                . 'Content-Disposition: form-data; name="' . self::quote($name) . '"' . "\r\n\r\n"
                . $value . "\r\n";
        }
        $body .= '--' . $boundary . "\r\n"
            . 'Content-Disposition: form-data; name="' . self::quote($fileField) . '"; filename="' . self::quote($fileName) . '"' . "\r\n"
            . 'Content-Type: ' . $fileContentType . "\r\n\r\n"
            . $fileContent . "\r\n"
            . '--' . $boundary . "--\r\n";

        return $this->send('POST', $path, [], $body, 'multipart/form-data; boundary=' . $boundary);
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<mixed>|null
     */
    private function send(string $method, string $path, array $query, ?string $body, ?string $contentType): ?array
    {
        $url = $this->baseUrl . $path;
        $query = array_filter($query, static fn ($v): bool => $v !== null && $v !== '');
        if ($query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => $this->userAgent,
            'Authorization' => 'Token ' . $this->token,
        ];
        if ($contentType !== null) {
            $headers['Content-Type'] = $contentType;
        }

        $response = $this->transport->send(new Request($method, $url, $headers, $body, $this->timeout));

        if ($response->statusCode >= 400) {
            throw ApiException::fromResponse($response);
        }
        if ($response->statusCode === 204 || $response->body === '') {
            return null;
        }

        try {
            $envelope = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (\JsonException $e) {
            throw new UnexpectedResponseException('interage: resposta da API não é JSON válido: ' . $e->getMessage(), 0, $e);
        }
        if (!is_array($envelope)) {
            throw new UnexpectedResponseException('interage: envelope da resposta em formato inesperado');
        }
        $data = $envelope['data'] ?? null;
        if ($data === null) {
            return null;
        }
        if (!is_array($data)) {
            throw new UnexpectedResponseException('interage: campo data da resposta em formato inesperado');
        }
        return $data;
    }

    /**
     * @param array<mixed>|null $body
     * @return array{0: ?string, 1: ?string}
     */
    private function json(?array $body): array
    {
        if ($body === null) {
            return [null, null];
        }
        return [
            json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'application/json; charset=utf-8',
        ];
    }

    private static function quote(string $value): string
    {
        return str_replace(['\\', '"', "\r", "\n"], ['\\\\', '\\"', '', ''], $value);
    }
}
