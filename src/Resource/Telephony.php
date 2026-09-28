<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Resource;

use WonitTecnologia\Interage\Exception\InterageException;
use WonitTecnologia\Interage\Exception\ValidationException;
use WonitTecnologia\Interage\Internal\HttpClient;
use WonitTecnologia\Interage\Model\ActiveCallList;
use WonitTecnologia\Interage\Model\CallHistory;
use WonitTecnologia\Interage\Model\Extension;
use WonitTecnologia\Interage\Model\OriginateCallResult;
use WonitTecnologia\Interage\Model\Page;
use WonitTecnologia\Interage\Model\TempLink;
use WonitTecnologia\Interage\Request\OriginateCallRequest;

/**
 * Ramais, histórico de ligações, click-to-call e gravações.
 */
final class Telephony
{
    private const PATH = '/api/public/pabx/telefonia';

    /** @internal Obtenha pelo Client: $cli->telephony. */
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Lista os ramais com status de registro SIP.
     *
     * @param string|null $search Número ou nome do ramal.
     * @return Page<Extension>
     * @throws InterageException
     */
    public function listExtensions(?string $search = null, ?int $page = null, ?int $pageSize = null): Page
    {
        $data = $this->http->get(self::PATH . '/ramais', ['search' => $search, 'page' => $page, 'page_size' => $pageSize]);
        return Page::fromArray($data ?? [], static fn (array $i): Extension => Extension::fromArray($i));
    }

    /**
     * Histórico de ligações. O período é obrigatório e vai de no máximo 3 meses.
     *
     * @param \DateTimeInterface $dateFrom   Início do período (dia).
     * @param \DateTimeInterface $dateTo     Fim do período (dia).
     * @param string|null        $extension  Ramal em origem OU destino (prefixo).
     * @param string|null        $callResult ANSWERED | NO_ANSWER | BUSY | FAILED | ABANDONED | VOICEMAIL.
     * @param string|null        $callType   inbound | outbound | internal.
     * @param string|null        $cursor     Page::$nextCursor da página anterior (ignora $page).
     * @return Page<CallHistory>
     * @throws InterageException
     */
    public function listCallHistory(
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo,
        ?string $callerIdNum = null,
        ?string $calledNumber = null,
        ?string $extension = null,
        ?string $callResult = null,
        ?string $callType = null,
        ?int $page = null,
        ?int $pageSize = null,
        ?string $cursor = null,
    ): Page {
        if ($dateTo < $dateFrom) {
            throw new ValidationException('interage: dateTo não pode ser anterior a dateFrom');
        }
        $data = $this->http->get(self::PATH . '/historico', [
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'caller_id_num' => $callerIdNum,
            'called_number' => $calledNumber,
            'extension' => $extension,
            'call_result' => $callResult,
            'call_type' => $callType,
            'page' => $page,
            'page_size' => $pageSize,
            'cursor' => $cursor,
        ]);
        return Page::fromArray($data ?? [], static fn (array $i): CallHistory => CallHistory::fromArray($i));
    }

    /**
     * Chamadas em curso (não paginado).
     *
     * @throws InterageException
     */
    public function listActiveCalls(): ActiveCallList
    {
        return ActiveCallList::fromArray($this->http->get(self::PATH . '/chamadas-ativas') ?? []);
    }

    /**
     * Click-to-call: o ramal toca e, ao atender, a chamada segue para o número.
     *
     * @throws \WonitTecnologia\Interage\Exception\ConflictException            ramal já em chamada.
     * @throws \WonitTecnologia\Interage\Exception\UnprocessableEntityException ramal não registrado.
     * @throws InterageException
     */
    public function originateCall(OriginateCallRequest $request): OriginateCallResult
    {
        return OriginateCallResult::fromArray($this->http->post(self::PATH . '/originate', $request->toArray()) ?? []);
    }

    /**
     * Gera link temporário de download da gravação de uma ligação.
     *
     * @param int|null $expiresIn Validade em segundos (padrão da API: 3600; máximo: 604800).
     * @throws InterageException
     */
    public function createRecordingTempLink(string $callId, ?int $expiresIn = null): TempLink
    {
        $data = $this->http->post(
            self::PATH . '/historico/' . rawurlencode($callId) . '/recording/templink',
            null,
            ['expires_in' => $expiresIn],
        );
        return TempLink::fromArray($data ?? []);
    }
}
