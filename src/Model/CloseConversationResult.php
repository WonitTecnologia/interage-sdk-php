<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Retorno do encerramento de conversa.
 */
final class CloseConversationResult
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $protocol,
        public readonly string $status,
        public readonly ?\DateTimeImmutable $finishedAt,
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
            Data::nullableString($data, 'protocol'),
            Data::string($data, 'status'),
            Data::nullableDate($data, 'finished_at'),
        );
    }
}
