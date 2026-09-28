<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Mensagem do histórico de uma conversa.
 */
final class ConversationMessage
{
    /**
     * @param ?string $mediaUrl Caminho da mídia no bucket — para baixar, gere um link com Omni::createMessageFileTempLink().
     */
    public function __construct(
        public readonly int $id,
        public readonly string $direction,
        public readonly string $sentBy,
        public readonly string $messageType,
        public readonly ?string $content,
        public readonly ?string $caption,
        public readonly ?string $mediaUrl,
        public readonly ?string $mediaMime,
        public readonly string $status,
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
            Data::string($data, 'direction'),
            Data::string($data, 'sent_by'),
            Data::string($data, 'message_type'),
            Data::nullableString($data, 'content'),
            Data::nullableString($data, 'caption'),
            Data::nullableString($data, 'media_url'),
            Data::nullableString($data, 'media_mime'),
            Data::string($data, 'status'),
            Data::date($data, 'created_at'),
        );
    }
}
