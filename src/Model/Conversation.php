<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Conversa omni ativa.
 */
final class Conversation
{
    /**
     * @param string $status Status — ver {@see \WonitTecnologia\Interage\Enum\ConversationStatus}.
     */
    public function __construct(
        public readonly int $id,
        public readonly ?string $protocol,
        public readonly string $status,
        public readonly string $channelType,
        public readonly ?string $channelSource,
        public readonly string $contactPhone,
        public readonly ?string $contactName,
        public readonly ?string $assignedAgentId,
        public readonly ?string $assignedAgentName,
        public readonly ?int $queueId,
        public readonly ?string $lastMessage,
        public readonly ?\DateTimeImmutable $lastMessageAt,
        public readonly ?\DateTimeImmutable $queuedAt,
        public readonly ?\DateTimeImmutable $assignedAt,
        public readonly \DateTimeImmutable $createdAt,
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
            Data::nullableString($data, 'assigned_agent_id'),
            Data::nullableString($data, 'assigned_agent_name'),
            Data::nullableInt($data, 'queue_id'),
            Data::nullableString($data, 'last_message'),
            Data::nullableDate($data, 'last_message_at'),
            Data::nullableDate($data, 'queued_at'),
            Data::nullableDate($data, 'assigned_at'),
            Data::date($data, 'created_at'),
        );
    }
}
