<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Instância de canal WhatsApp do tenant. Use $instanceId na criação de campanhas e no envio de templates.
 */
final class Instance
{
    public function __construct(
        public readonly string $id,
        public readonly string $instanceId,
        public readonly string $name,
        public readonly string $source,
        public readonly string $channelType,
        public readonly bool $active,
        public readonly bool $receptive,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'id'),
            Data::string($data, 'instance_id'),
            Data::string($data, 'name'),
            Data::string($data, 'source'),
            Data::string($data, 'channel_type'),
            Data::bool($data, 'active'),
            Data::bool($data, 'receptive'),
        );
    }
}
