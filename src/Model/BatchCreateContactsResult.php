<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Resultado da criação de contatos em lote. Cada item é processado de forma independente.
 */
final class BatchCreateContactsResult
{
    /**
     * @param list<BatchContactResult> $items
     */
    public function __construct(
        public readonly int $total,
        public readonly int $created,
        public readonly int $updated,
        public readonly int $existing,
        public readonly int $errors,
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
            Data::int($data, 'created'),
            Data::int($data, 'updated'),
            Data::int($data, 'existing'),
            Data::int($data, 'errors'),
            Data::list($data, 'items', static fn (array $i): BatchContactResult => BatchContactResult::fromArray($i)),
        );
    }
}
