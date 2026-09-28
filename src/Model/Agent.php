<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Agente do tenant com status de presença.
 */
final class Agent
{
    public function __construct(
        public readonly string $userId,
        public readonly string $username,
        public readonly string $name,
        public readonly bool $isOnline,
        public readonly bool $isPaused,
        public readonly ?string $pauseReason,
        public readonly ?\DateTimeImmutable $loggedInAt,
        public readonly ?\DateTimeImmutable $pausedAt,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'user_id'),
            Data::string($data, 'username'),
            Data::string($data, 'name'),
            Data::bool($data, 'is_online'),
            Data::bool($data, 'is_paused'),
            Data::nullableString($data, 'pause_reason'),
            Data::nullableDate($data, 'logged_in_at'),
            Data::nullableDate($data, 'paused_at'),
        );
    }
}
