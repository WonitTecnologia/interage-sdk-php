<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Chamada em curso.
 */
final class ActiveCall
{
    /**
     * @param int $duration Segundos.
     */
    public function __construct(
        public readonly string $callId,
        public readonly string $uniqueId,
        public readonly string $channel,
        public readonly ?string $protocolNumber,
        public readonly ?string $direction,
        public readonly ?string $callerIdNum,
        public readonly ?string $callerIdName,
        public readonly ?string $calledNumber,
        public readonly ?string $destinationNum,
        public readonly ?string $didNumber,
        public readonly ?string $trunkName,
        public readonly string $status,
        public readonly \DateTimeImmutable $startTime,
        public readonly ?\DateTimeImmutable $answerTime,
        public readonly int $duration,
        public readonly ?string $queueName,
        public readonly ?string $agentExtension,
        public readonly ?string $agentName,
        public readonly ?\DateTimeImmutable $agentAnsweredAt,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'call_id'),
            Data::string($data, 'unique_id'),
            Data::string($data, 'channel'),
            Data::nullableString($data, 'protocol_number'),
            Data::nullableString($data, 'direction'),
            Data::nullableString($data, 'caller_id_num'),
            Data::nullableString($data, 'caller_id_name'),
            Data::nullableString($data, 'called_number'),
            Data::nullableString($data, 'destination_num'),
            Data::nullableString($data, 'did_number'),
            Data::nullableString($data, 'trunk_name'),
            Data::string($data, 'status'),
            Data::date($data, 'start_time'),
            Data::nullableDate($data, 'answer_time'),
            Data::int($data, 'duration'),
            Data::nullableString($data, 'queue_name'),
            Data::nullableString($data, 'agent_extension'),
            Data::nullableString($data, 'agent_name'),
            Data::nullableDate($data, 'agent_answered_at'),
        );
    }
}
