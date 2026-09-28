<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Exception;

/**
 * Falha de rede ou de conexão — a requisição não chegou a ter resposta da API.
 */
class TransportException extends \RuntimeException implements InterageException
{
}
