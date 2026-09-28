<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Retorno do click-to-call: a chamada foi aceita e o ramal vai tocar.
 */
final class OriginateCallResult
{
    public function __construct(
        public readonly string $actionId,
        public readonly string $channel,
        public readonly string $extension,
        public readonly string $toNumber,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'action_id'),
            Data::string($data, 'channel'),
            Data::string($data, 'extension'),
            Data::string($data, 'to_number'),
        );
    }
}
