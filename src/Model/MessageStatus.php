<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Status de entrega de uma mensagem enviada.
 */
final class MessageStatus
{
    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly ?\DateTimeImmutable $sentAt,
        public readonly ?\DateTimeImmutable $deliveredAt,
        public readonly ?\DateTimeImmutable $readAt,
        public readonly ?\DateTimeImmutable $failedAt,
        public readonly ?string $errorCode,
        public readonly ?string $errorDetails,
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
            Data::string($data, 'status'),
            Data::nullableDate($data, 'sent_at'),
            Data::nullableDate($data, 'delivered_at'),
            Data::nullableDate($data, 'read_at'),
            Data::nullableDate($data, 'failed_at'),
            Data::nullableString($data, 'error_code'),
            Data::nullableString($data, 'error_details'),
        );
    }
}
