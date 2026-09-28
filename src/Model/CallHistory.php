<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Ligação do histórico.
 */
final class CallHistory
{
    /**
     * @param ?int $duration Segundos.
     * @param ?int $queueWaitTime Segundos.
     */
    public function __construct(
        public readonly string $callId,
        public readonly string $uniqueId,
        public readonly ?string $linkedId,
        public readonly ?string $callerIdNum,
        public readonly ?string $callerIdName,
        public readonly ?string $calledNumber,
        public readonly ?string $destinationNum,
        public readonly ?string $didNumber,
        public readonly ?string $callType,
        public readonly ?string $routeType,
        public readonly ?string $trunkName,
        public readonly string $status,
        public readonly ?string $callResult,
        public readonly ?string $hangupCause,
        public readonly bool $isAbandoned,
        public readonly ?string $abandonReason,
        public readonly \DateTimeImmutable $startTime,
        public readonly ?\DateTimeImmutable $answerTime,
        public readonly ?\DateTimeImmutable $endTime,
        public readonly ?int $duration,
        public readonly ?int $billableSec,
        public readonly ?int $queueWaitTime,
        public readonly ?string $queueName,
        public readonly ?string $agentExtension,
        public readonly ?string $agentName,
        public readonly ?\DateTimeImmutable $agentAnsweredAt,
        public readonly ?bool $recordingEnabled,
        public readonly ?string $protocolNumber,
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
            Data::nullableString($data, 'linked_id'),
            Data::nullableString($data, 'caller_id_num'),
            Data::nullableString($data, 'caller_id_name'),
            Data::nullableString($data, 'called_number'),
            Data::nullableString($data, 'destination_num'),
            Data::nullableString($data, 'did_number'),
            Data::nullableString($data, 'call_type'),
            Data::nullableString($data, 'route_type'),
            Data::nullableString($data, 'trunk_name'),
            Data::string($data, 'status'),
            Data::nullableString($data, 'call_result'),
            Data::nullableString($data, 'hangup_cause'),
            Data::bool($data, 'is_abandoned'),
            Data::nullableString($data, 'abandon_reason'),
            Data::date($data, 'start_time'),
            Data::nullableDate($data, 'answer_time'),
            Data::nullableDate($data, 'end_time'),
            Data::nullableInt($data, 'duration'),
            Data::nullableInt($data, 'billable_sec'),
            Data::nullableInt($data, 'queue_wait_time'),
            Data::nullableString($data, 'queue_name'),
            Data::nullableString($data, 'agent_extension'),
            Data::nullableString($data, 'agent_name'),
            Data::nullableDate($data, 'agent_answered_at'),
            Data::nullableBool($data, 'recording_enabled'),
            Data::nullableString($data, 'protocol_number'),
        );
    }
}
