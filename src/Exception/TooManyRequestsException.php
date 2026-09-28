<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Exception;

/**
 * 429 — limite de requisições excedido (por token ou por IP). Aguarde {@see ApiException::$retryAfter} segundos antes de tentar de novo; o SDK não repete a requisição sozinho.
 */
class TooManyRequestsException extends ApiException
{
}
