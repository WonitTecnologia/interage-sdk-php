<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Exception;

use WonitTecnologia\Interage\Http\Response;

/**
 * Erro HTTP (status >= 400) devolvido pela API. Cada status tem uma subclasse
 * própria ({@see NotFoundException}, {@see ForbiddenException}, ...) — capture a
 * subclasse para tratar um caso específico, ou esta classe para qualquer erro da API.
 */
class ApiException extends \RuntimeException implements InterageException
{
    /**
     * @param int         $statusCode Status HTTP da resposta.
     * @param string      $apiStatus  Rótulo do erro no corpo (ex.: "NOT_FOUND").
     * @param string      $apiMessage Mensagem legível da API (pt-BR).
     * @param int|null    $retryAfter Segundos a esperar antes de tentar de novo (header Retry-After).
     * @param string      $rawBody    Corpo bruto da resposta.
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly string $apiStatus,
        public readonly string $apiMessage,
        public readonly ?int $retryAfter,
        public readonly string $rawBody,
    ) {
        parent::__construct(
            sprintf('interage: API retornou %d (%s): %s', $statusCode, $apiStatus, $apiMessage),
            $statusCode,
        );
    }

    /**
     * Monta a exceção da subclasse correspondente ao status da resposta.
     */
    public static function fromResponse(Response $response): self
    {
        $payload = json_decode($response->body, true);
        $payload = is_array($payload) ? $payload : [];

        $apiStatus = isset($payload['status']) && is_string($payload['status']) && $payload['status'] !== ''
            ? $payload['status']
            : 'HTTP_' . $response->statusCode;
        $apiMessage = isset($payload['message']) && is_string($payload['message']) && $payload['message'] !== ''
            ? $payload['message']
            : $response->body;

        $retryAfter = null;
        $header = $response->header('Retry-After');
        if ($header !== null && ctype_digit($header) && (int) $header > 0) {
            $retryAfter = (int) $header;
        }

        $class = match (true) {
            $response->statusCode === 400 => BadRequestException::class,
            $response->statusCode === 401 => UnauthorizedException::class,
            $response->statusCode === 403 => ForbiddenException::class,
            $response->statusCode === 404 => NotFoundException::class,
            $response->statusCode === 409 => ConflictException::class,
            $response->statusCode === 422 => UnprocessableEntityException::class,
            $response->statusCode === 429 => TooManyRequestsException::class,
            $response->statusCode >= 500 => ServerException::class,
            default => self::class,
        };

        return new $class($response->statusCode, $apiStatus, $apiMessage, $retryAfter, $response->body);
    }
}
