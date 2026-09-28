<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Request;

use WonitTecnologia\Interage\Enum\TransferTargetType;

/**
 * Destino de uma transferência de conversa. Use toQueue() ou toUser().
 */
final class TransferConversationRequest
{
    private function __construct(
        public readonly TransferTargetType $targetType,
        public readonly ?int $queueId,
        public readonly ?string $userId,
    ) {
    }

    /** Transfere para uma fila (Omni::listQueues). */
    public static function toQueue(int $queueId): self
    {
        return new self(TransferTargetType::Queue, $queueId, null);
    }

    /** Transfere para um agente (Omni::listAgents). */
    public static function toUser(string $userId): self
    {
        return new self(TransferTargetType::User, null, $userId);
    }

    /**
     * @internal
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Payload::compact([
            'target_type' => $this->targetType->value,
            'queue_id' => $this->queueId,
            'user_id' => $this->userId,
        ]);
    }
}
