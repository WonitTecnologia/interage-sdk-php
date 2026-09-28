<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Exception;

/**
 * A API respondeu com sucesso, mas num formato que o SDK não reconhece
 * (JSON inválido, envelope ausente, data em formato inesperado).
 */
class UnexpectedResponseException extends \UnexpectedValueException implements InterageException
{
}
