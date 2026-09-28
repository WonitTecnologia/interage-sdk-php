<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Request;

use WonitTecnologia\Interage\Exception\ValidationException;

/**
 * Click-to-call: o ramal de origem toca e, ao atender, a chamada segue para o número.
 */
final class OriginateCallRequest
{
    /**
     * @param string $fromExtension Ramal que vai originar a chamada (precisa estar registrado).
     * @param string $toNumber      Número de destino.
     */
    public function __construct(
        public readonly string $fromExtension,
        public readonly string $toNumber,
    ) {
    }

    /**
     * @internal
     * @return array<string, string>
     * @throws ValidationException
     */
    public function toArray(): array
    {
        if (trim($this->fromExtension) === '' || trim($this->toNumber) === '') {
            throw new ValidationException('interage: OriginateCallRequest::$fromExtension e $toNumber são obrigatórios');
        }
        return ['from_extension' => $this->fromExtension, 'to_number' => $this->toNumber];
    }
}
