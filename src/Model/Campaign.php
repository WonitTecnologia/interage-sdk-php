<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Campanha de disparo WhatsApp (listagem e detalhe).
 */
final class Campaign
{
    /**
     * @param string $status Status da campanha — ver {@see \WonitTecnologia\Interage\Enum\CampaignStatus}.
     * @param bool $replyWithoutContext Mensagem do contato sem citar o disparo nem clicar em botão também conta como resposta (repliedCount), se chegar dentro de $replyWindowHours.
     * @param int $replyWindowHours Janela, em horas após o disparo, em que a resposta sem citação conta (1 a 72; padrão 24).
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $channel,
        public readonly string $status,
        public readonly ?string $templateId,
        public readonly ?\DateTimeImmutable $scheduledAt,
        public readonly ?\DateTimeImmutable $startAt,
        public readonly ?\DateTimeImmutable $endAt,
        public readonly int $totalContacts,
        public readonly int $sentCount,
        public readonly int $deliveredCount,
        public readonly int $readCount,
        public readonly int $repliedCount,
        public readonly int $failedCount,
        public readonly bool $replyWithoutContext,
        public readonly int $replyWindowHours,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
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
            Data::string($data, 'name'),
            Data::nullableString($data, 'description'),
            Data::string($data, 'channel'),
            Data::string($data, 'status'),
            Data::nullableString($data, 'template_id'),
            Data::nullableDate($data, 'scheduled_at'),
            Data::nullableDate($data, 'start_at'),
            Data::nullableDate($data, 'end_at'),
            Data::int($data, 'total_contacts'),
            Data::int($data, 'sent_count'),
            Data::int($data, 'delivered_count'),
            Data::int($data, 'read_count'),
            Data::int($data, 'replied_count'),
            Data::int($data, 'failed_count'),
            Data::bool($data, 'reply_without_context'),
            Data::int($data, 'reply_window_hours'),
            Data::date($data, 'created_at'),
            Data::date($data, 'updated_at'),
        );
    }
}
