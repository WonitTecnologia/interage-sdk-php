<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Resource;

use WonitTecnologia\Interage\Enum\ConversationStatus;
use WonitTecnologia\Interage\Exception\InterageException;
use WonitTecnologia\Interage\Internal\HttpClient;
use WonitTecnologia\Interage\Model\Agent;
use WonitTecnologia\Interage\Model\CloseConversationResult;
use WonitTecnologia\Interage\Model\Conversation;
use WonitTecnologia\Interage\Model\ConversationHistory;
use WonitTecnologia\Interage\Model\Page;
use WonitTecnologia\Interage\Model\Queue;
use WonitTecnologia\Interage\Model\TempLink;
use WonitTecnologia\Interage\Model\TransferConversationResult;
use WonitTecnologia\Interage\Request\TransferConversationRequest;

/**
 * Filas, agentes e conversas do atendimento omnichannel.
 */
final class Omni
{
    private const PATH = '/api/public/omni/administrativo';

    /** @internal Obtenha pelo Client: $cli->omni. */
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Lista as filas omni com seus agentes e presença.
     *
     * @return Page<Queue>
     * @throws InterageException
     */
    public function listQueues(?int $page = null, ?int $pageSize = null): Page
    {
        $data = $this->http->get(self::PATH . '/queues', ['page' => $page, 'page_size' => $pageSize]);
        return Page::fromArray($data ?? [], static fn (array $i): Queue => Queue::fromArray($i));
    }

    /**
     * Lista os agentes do tenant com presença em tempo real.
     *
     * @return Page<Agent>
     * @throws InterageException
     */
    public function listAgents(?int $page = null, ?int $pageSize = null): Page
    {
        $data = $this->http->get(self::PATH . '/agents', ['page' => $page, 'page_size' => $pageSize]);
        return Page::fromArray($data ?? [], static fn (array $i): Agent => Agent::fromArray($i));
    }

    /**
     * Lista as conversas ativas.
     *
     * @param ConversationStatus|string|null $status      Vazio = todas as ativas.
     * @param string|null                    $channelType whatsapp | telegram | instagram | email.
     * @return Page<Conversation>
     * @throws InterageException
     */
    public function listConversations(
        ConversationStatus|string|null $status = null,
        ?string $channelType = null,
        ?int $page = null,
        ?int $pageSize = null,
    ): Page {
        $data = $this->http->get(self::PATH . '/conversations', [
            'status' => $status instanceof ConversationStatus ? $status->value : $status,
            'channel_type' => $channelType,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
        return Page::fromArray($data ?? [], static fn (array $i): Conversation => Conversation::fromArray($i));
    }

    /**
     * Mensagens de uma conversa pelo protocolo, paginado.
     *
     * @throws InterageException
     */
    public function getConversationHistory(string $protocol, ?int $page = null, ?int $pageSize = null): ConversationHistory
    {
        $data = $this->http->get(
            self::PATH . '/conversations/' . rawurlencode($protocol) . '/history',
            ['page' => $page, 'page_size' => $pageSize],
        );
        return ConversationHistory::fromArray($data ?? []);
    }

    /**
     * Encerra uma conversa ativa.
     *
     * @throws InterageException
     */
    public function closeConversation(string $protocol): CloseConversationResult
    {
        $data = $this->http->post(self::PATH . '/conversations/' . rawurlencode($protocol) . '/close');
        return CloseConversationResult::fromArray($data ?? []);
    }

    /**
     * Transfere a conversa para uma fila ou um agente.
     *
     *     $cli->omni->transferConversation($protocolo, TransferConversationRequest::toQueue(3));
     *
     * @throws InterageException
     */
    public function transferConversation(string $protocol, TransferConversationRequest $request): TransferConversationResult
    {
        $data = $this->http->post(self::PATH . '/conversations/' . rawurlencode($protocol) . '/transfer', $request->toArray());
        return TransferConversationResult::fromArray($data ?? []);
    }

    /**
     * Gera link temporário de download do arquivo de uma mensagem.
     *
     * @param int|null $expiresIn Validade em segundos (padrão da API: 3600; máximo: 604800).
     * @throws InterageException
     */
    public function createMessageFileTempLink(int $messageId, ?int $expiresIn = null): TempLink
    {
        $data = $this->http->post(
            self::PATH . '/conversations/messages/' . $messageId . '/templink',
            null,
            ['expires_in' => $expiresIn],
        );
        return TempLink::fromArray($data ?? []);
    }
}
