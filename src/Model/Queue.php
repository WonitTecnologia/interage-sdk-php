<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Fila omni com seus agentes.
 */
final class Queue
{
    /**
     * @param list<QueueUser> $users
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $channelType,
        public readonly string $strategy,
        public readonly bool $isActive,
        public readonly array $users,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::int($data, 'id'),
            Data::string($data, 'name'),
            Data::string($data, 'channel_type'),
            Data::string($data, 'strategy'),
            Data::bool($data, 'is_active'),
            Data::list($data, 'users', static fn (array $i): QueueUser => QueueUser::fromArray($i)),
        );
    }
}
