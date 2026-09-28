<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Retorno da transferência de conversa.
 */
final class TransferConversationResult
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $protocol,
        public readonly string $status,
        public readonly string $targetType,
        public readonly ?int $queueId,
        public readonly ?string $userId,
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
            Data::string($data, 'target_type'),
            Data::nullableInt($data, 'queue_id'),
            Data::nullableString($data, 'user_id'),
        );
    }
}
