<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Página de uma listagem.
 *
 * Paginação por cursor (campanhas, contatos e histórico de ligações): passe
 * $nextCursor como `cursor` da próxima chamada; null indica a última página.
 * Com `cursor` informado, a API ignora `page`.
 *
 * @template T
 */
final class Page
{
    /**
     * @param list<T> $items
     */
    public function __construct(
        public readonly int $total,
        public readonly int $page,
        public readonly int $pageSize,
        public readonly ?string $nextCursor,
        public readonly array $items,
    ) {
    }

    /**
     * @template U
     * @param array<mixed>               $data
     * @param callable(array<mixed>): U  $map
     * @return self<U>
     */
    public static function fromArray(array $data, callable $map): self
    {
        $cursor = Data::nullableString($data, 'next_cursor');
        return new self(
            Data::int($data, 'total'),
            Data::int($data, 'page'),
            Data::int($data, 'page_size'),
            $cursor === '' ? null : $cursor,
            Data::list($data, 'items', $map),
        );
    }
}
