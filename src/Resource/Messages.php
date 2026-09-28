<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Resource;

use WonitTecnologia\Interage\Exception\InterageException;
use WonitTecnologia\Interage\Internal\HttpClient;
use WonitTecnologia\Interage\Model\Instance;
use WonitTecnologia\Interage\Model\MessageStatus;
use WonitTecnologia\Interage\Model\Page;
use WonitTecnologia\Interage\Model\SendMessageResult;
use WonitTecnologia\Interage\Model\SendTemplateResult;
use WonitTecnologia\Interage\Model\Template;
use WonitTecnologia\Interage\Request\SendMessageRequest;
use WonitTecnologia\Interage\Request\SendTemplateRequest;

/**
 * Instâncias, templates HSM e envio de mensagens WhatsApp.
 */
final class Messages
{
    private const PATH = '/api/public/whatsapp/messages';

    /** @internal Obtenha pelo Client: $cli->messages. */
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Lista as instâncias de canal WhatsApp do tenant.
     *
     * @return Page<Instance>
     * @throws InterageException
     */
    public function listInstances(?int $page = null, ?int $pageSize = null): Page
    {
        $data = $this->http->get(self::PATH . '/instances', ['page' => $page, 'page_size' => $pageSize]);
        return Page::fromArray($data ?? [], static fn (array $i): Instance => Instance::fromArray($i));
    }

    /**
     * Lista os templates HSM do tenant.
     *
     * @param string|null $status   APPROVED | PENDING | REJECTED.
     * @param string|null $category MARKETING | UTILITY | AUTHENTICATION.
     * @param string|null $type     TEXT | IMAGE | VIDEO | DOCUMENT | AUDIO.
     * @return Page<Template>
     * @throws InterageException
     */
    public function listTemplates(
        ?string $status = null,
        ?string $category = null,
        ?string $type = null,
        ?string $instanceId = null,
        ?int $page = null,
        ?int $pageSize = null,
    ): Page {
        $data = $this->http->get(self::PATH . '/templates', [
            'status' => $status,
            'category' => $category,
            'type' => $type,
            'instance_id' => $instanceId,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
        return Page::fromArray($data ?? [], static fn (array $i): Template => Template::fromArray($i));
    }

    /**
     * Envia um template HSM aprovado.
     *
     * @throws InterageException
     */
    public function sendTemplate(SendTemplateRequest $request): SendTemplateResult
    {
        return SendTemplateResult::fromArray($this->http->post(self::PATH . '/templates/send', $request->toArray()) ?? []);
    }

    /**
     * Envia uma mensagem não-template sobre uma conversa com sessão ativa de 24h.
     *
     * @throws InterageException
     */
    public function sendMessage(SendMessageRequest $request): SendMessageResult
    {
        return SendMessageResult::fromArray($this->http->post(self::PATH . '/send', $request->toArray()) ?? []);
    }

    /**
     * Consulta o status de entrega pelo ID interno (internalMessageId dos envios).
     *
     * @throws InterageException
     */
    public function getMessageStatus(int $internalMessageId): MessageStatus
    {
        return MessageStatus::fromArray($this->http->get(self::PATH . '/status', ['message_id' => $internalMessageId]) ?? []);
    }
}
