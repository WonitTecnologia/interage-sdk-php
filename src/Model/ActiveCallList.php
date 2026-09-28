<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Chamadas em curso (listagem não paginada).
 */
final class ActiveCallList
{
    /**
     * @param list<ActiveCall> $items
     */
    public function __construct(
        public readonly int $total,
        public readonly array $items,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::int($data, 'total'),
            Data::list($data, 'items', static fn (array $i): ActiveCall => ActiveCall::fromArray($i)),
        );
    }
}
