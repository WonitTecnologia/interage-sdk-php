<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Tests\Support;

use WonitTecnologia\Interage\Http\Request;
use WonitTecnologia\Interage\Http\Response;
use WonitTecnologia\Interage\Http\Transport;

/**
 * Transporte em memória: registra as requisições e devolve respostas enfileiradas.
 */
final class FakeTransport implements Transport
{
    /** @var list<Request> */
    public array $requests = [];

    /** @var list<Response> */
    private array $queue = [];

    /**
     * @param array<mixed>|null     $data    Conteúdo de `data` no envelope de sucesso.
     * @param array<string, string> $headers
     */
    public function respond(?array $data, int $status = 200, array $headers = []): self
    {
        $body = json_encode(['code' => $status, 'status' => 'OK', 'message' => 'ok', 'data' => $data], JSON_THROW_ON_ERROR);
        $this->queue[] = new Response($status, $headers, $body);
        return $this;
    }

    /** @param array<string, string> $headers */
    public function respondRaw(int $status, string $body, array $headers = []): self
    {
        $this->queue[] = new Response($status, $headers, $body);
        return $this;
    }

    public function send(Request $request): Response
    {
        $this->requests[] = $request;
        $response = array_shift($this->queue);
        if ($response === null) {
            throw new \LogicException('FakeTransport sem resposta enfileirada para ' . $request->method . ' ' . $request->url);
        }
        return $response;
    }

    public function last(): Request
    {
        $last = end($this->requests);
        if ($last === false) {
            throw new \LogicException('nenhuma requisição registrada');
        }
        return $last;
    }

    /** @return array<string, string> Query da última requisição. */
    public function lastQuery(): array
    {
        parse_str((string) parse_url($this->last()->url, PHP_URL_QUERY), $query);
        /** @var array<string, string> $query */
        return $query;
    }

    public function lastPath(): string
    {
        return (string) parse_url($this->last()->url, PHP_URL_PATH);
    }

    /** @return array<mixed> Corpo JSON da última requisição. */
    public function lastJson(): array
    {
        return json_decode((string) $this->last()->body, true, 512, JSON_THROW_ON_ERROR);
    }
}
