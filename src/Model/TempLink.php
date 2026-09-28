<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Link público temporário de download (arquivo de mensagem ou gravação).
 */
final class TempLink
{
    /**
     * @param int $expiresIn Validade em segundos.
     */
    public function __construct(
        public readonly string $token,
        public readonly string $url,
        public readonly \DateTimeImmutable $expiresAt,
        public readonly int $expiresIn,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'token'),
            Data::string($data, 'url'),
            Data::date($data, 'expires_at'),
            Data::int($data, 'expires_in'),
        );
    }
}
