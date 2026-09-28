<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Internal;

use WonitTecnologia\Interage\Exception\UnexpectedResponseException;

/**
 * Leitura tipada dos arrays decodificados da API, usada pelos fromArray() dos modelos.
 *
 * Campos obrigatórios ausentes seguem a mesma semântica do SDK Go (valor zero do
 * tipo); campos que a API pode devolver como null são expostos como null — sem
 * confundir "não informado" com string vazia ou zero.
 *
 * @internal Não faz parte da API pública do SDK.
 */
final class Data
{
    /** @param array<mixed> $d */
    public static function string(array $d, string $key): string
    {
        return isset($d[$key]) ? (string) $d[$key] : '';
    }

    /** @param array<mixed> $d */
    public static function nullableString(array $d, string $key): ?string
    {
        return isset($d[$key]) ? (string) $d[$key] : null;
    }

    /** @param array<mixed> $d */
    public static function int(array $d, string $key): int
    {
        return isset($d[$key]) ? (int) $d[$key] : 0;
    }

    /** @param array<mixed> $d */
    public static function nullableInt(array $d, string $key): ?int
    {
        return isset($d[$key]) ? (int) $d[$key] : null;
    }

    /** @param array<mixed> $d */
    public static function bool(array $d, string $key): bool
    {
        return isset($d[$key]) && (bool) $d[$key];
    }

    /** @param array<mixed> $d */
    public static function nullableBool(array $d, string $key): ?bool
    {
        return isset($d[$key]) ? (bool) $d[$key] : null;
    }

    /**
     * Data obrigatória. Ausente ou inválida indica resposta fora do contrato.
     *
     * @param array<mixed> $d
     */
    public static function date(array $d, string $key): \DateTimeImmutable
    {
        $date = self::nullableDate($d, $key);
        if ($date === null) {
            throw new UnexpectedResponseException(sprintf('interage: campo de data "%s" ausente na resposta', $key));
        }
        return $date;
    }

    /** @param array<mixed> $d */
    public static function nullableDate(array $d, string $key): ?\DateTimeImmutable
    {
        if (!isset($d[$key]) || $d[$key] === '') {
            return null;
        }
        return self::parseDate((string) $d[$key], $key);
    }

    /**
     * Converte uma lista de objetos da resposta em modelos.
     *
     * @template T
     * @param array<mixed>               $d
     * @param callable(array<mixed>): T  $map
     * @return list<T>
     */
    public static function list(array $d, string $key, callable $map): array
    {
        if (!isset($d[$key]) || !is_array($d[$key])) {
            return [];
        }
        $out = [];
        foreach ($d[$key] as $item) {
            if (is_array($item)) {
                $out[] = $map($item);
            }
        }
        return $out;
    }

    /**
     * Objeto JSON livre (ex.: custom_info).
     *
     * @param array<mixed> $d
     * @return array<string, mixed>
     */
    public static function map(array $d, string $key): array
    {
        return isset($d[$key]) && is_array($d[$key]) ? $d[$key] : [];
    }

    /**
     * Datas da API (Go, RFC 3339) podem ter até 9 casas de fração de segundo;
     * o PHP aceita no máximo 6 — o excedente é truncado.
     */
    private static function parseDate(string $value, string $key): \DateTimeImmutable
    {
        $normalized = (string) preg_replace('/(\.\d{6})\d+/', '$1', $value);
        try {
            return new \DateTimeImmutable($normalized);
        } catch (\Exception $e) {
            throw new UnexpectedResponseException(sprintf('interage: data inválida em "%s": %s', $key, $value), 0, $e);
        }
    }
}
