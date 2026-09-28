<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Retorno do envio de mensagem não-template.
 */
final class SendMessageResult
{
    /**
     * @param int $internalMessageId ID interno da mensagem — use em Messages::getMessageStatus().
     */
    public function __construct(
        public readonly string $messageId,
        public readonly int $internalMessageId,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'message_id'),
            Data::int($data, 'internal_message_id'),
        );
    }
}
