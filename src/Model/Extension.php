<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Ramal da central telefônica com status de registro SIP.
 */
final class Extension
{
    public function __construct(
        public readonly string $number,
        public readonly ?string $name,
        public readonly ?string $callerId,
        public readonly bool $isOnline,
        public readonly bool $dndEnabled,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'number'),
            Data::nullableString($data, 'name'),
            Data::nullableString($data, 'caller_id'),
            Data::bool($data, 'is_online'),
            Data::bool($data, 'dnd_enabled'),
        );
    }
}
