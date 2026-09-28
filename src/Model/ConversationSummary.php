<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Metadados da conversa no histórico.
 */
final class ConversationSummary
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $protocol,
        public readonly string $status,
        public readonly string $channelType,
        public readonly ?string $channelSource,
        public readonly string $contactPhone,
        public readonly ?string $contactName,
        public readonly ?string $assignedAgentName,
        public readonly ?int $queueId,
        public readonly string $orientation,
        public readonly \DateTimeImmutable $createdAt,
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
            Data::string($data, 'channel_type'),
            Data::nullableString($data, 'channel_source'),
            Data::string($data, 'contact_phone'),
            Data::nullableString($data, 'contact_name'),
            Data::nullableString($data, 'assigned_agent_name'),
            Data::nullableInt($data, 'queue_id'),
            Data::string($data, 'orientation'),
            Data::date($data, 'created_at'),
            Data::nullableDate($data, 'finished_at'),
        );
    }
}
