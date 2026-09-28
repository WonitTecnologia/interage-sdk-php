<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Histórico paginado de uma conversa.
 */
final class ConversationHistory
{
    /**
     * @param list<ConversationMessage> $items
     */
    public function __construct(
        public readonly ConversationSummary $conversation,
        public readonly int $total,
        public readonly int $page,
        public readonly int $pageSize,
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
            ConversationSummary::fromArray(Data::map($data, 'conversation')),
            Data::int($data, 'total'),
            Data::int($data, 'page'),
            Data::int($data, 'page_size'),
            Data::list($data, 'items', static fn (array $i): ConversationMessage => ConversationMessage::fromArray($i)),
        );
    }
}
