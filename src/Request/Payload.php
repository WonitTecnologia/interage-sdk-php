<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Request;

/**
 * @internal Não faz parte da API pública do SDK.
 */
final class Payload
{
    /**
     * Remove campos opcionais não informados (null, '' ou lista vazia), como o
     * `omitempty` do SDK Go — a API aplica o padrão dela para o que não vier.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public static function compact(array $payload): array
    {
        return array_filter($payload, static fn ($v): bool => $v !== null && $v !== '' && $v !== []);
    }
}
