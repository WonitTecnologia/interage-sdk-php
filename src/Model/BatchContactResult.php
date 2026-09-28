<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Resultado do processamento de um contato do lote.
 */
final class BatchContactResult
{
    /**
     * @param int $index Posição do contato no lote enviado (0-based).
     * @param string $status created | existing | updated | error.
     * @param ?string $message Motivo, quando status = error.
     */
    public function __construct(
        public readonly int $index,
        public readonly string $status,
        public readonly ?string $contactId,
        public readonly ?string $message,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::int($data, 'index'),
            Data::string($data, 'status'),
            Data::nullableString($data, 'contact_id'),
            Data::nullableString($data, 'message'),
        );
    }
}
