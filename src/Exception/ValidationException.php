<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Exception;

/**
 * Parâmetro inválido detectado pelo próprio SDK, antes de chamar a API.
 */
class ValidationException extends \InvalidArgumentException implements InterageException
{
}
