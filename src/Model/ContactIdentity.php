<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Forma de contato de um contato (telefone, WhatsApp, Telegram...).
 */
final class ContactIdentity
{
    public function __construct(
        public readonly string $channel,
        public readonly string $idType,
        public readonly string $idValue,
        public readonly bool $isPrimary,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'channel'),
            Data::string($data, 'id_type'),
            Data::string($data, 'id_value'),
            Data::bool($data, 'is_primary'),
        );
    }
}
