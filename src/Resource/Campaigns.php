<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Resource;

use WonitTecnologia\Interage\Enum\CampaignStatus;
use WonitTecnologia\Interage\Exception\ApiException;
use WonitTecnologia\Interage\Exception\InterageException;
use WonitTecnologia\Interage\Internal\HttpClient;
use WonitTecnologia\Interage\Model\Campaign;
use WonitTecnologia\Interage\Model\CreateCampaignResult;
use WonitTecnologia\Interage\Model\Page;
use WonitTecnologia\Interage\Request\CreateCampaignRequest;

/**
 * Campanhas de disparo WhatsApp.
 */
final class Campaigns
{
    private const PATH = '/api/public/whatsapp/campanhas';

    /** @internal Obtenha pelo Client: $cli->campaigns. */
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Cria uma campanha com o CSV de contatos anexo. A importação é assíncrona:
     * a campanha nasce "pending" e passa a "ready" quando o CSV termina de importar.
     *
     * @throws InterageException
     */
    public function create(CreateCampaignRequest $request): CreateCampaignResult
    {
        $data = $this->http->postMultipart(
            self::PATH,
            $request->toFields(),
            'file',
            $request->fileName,
            $request->fileContent,
            'text/csv',
        );
        return CreateCampaignResult::fromArray($data ?? []);
    }

    /**
     * Lista as campanhas do tenant.
     *
     * @param string|null                $search   Busca por nome.
     * @param CampaignStatus|string|null $status   Filtra por status.
     * @param int|null                   $pageSize Padrão 10, máximo 100.
     * @param string|null                $cursor   Page::$nextCursor da página anterior (ignora $page).
     * @return Page<Campaign>
     * @throws InterageException
     */
    public function list(
        ?string $search = null,
        CampaignStatus|string|null $status = null,
        ?int $page = null,
        ?int $pageSize = null,
        ?string $cursor = null,
    ): Page {
        $data = $this->http->get(self::PATH, [
            'search' => $search,
            'status' => $status instanceof CampaignStatus ? $status->value : $status,
            'page' => $page,
            'page_size' => $pageSize,
            'cursor' => $cursor,
        ]);
        return Page::fromArray($data ?? [], static fn (array $i): Campaign => Campaign::fromArray($i));
    }

    /**
     * @throws InterageException
     */
    public function get(string $id): Campaign
    {
        return Campaign::fromArray($this->http->get(self::PATH . '/' . rawurlencode($id)) ?? []);
    }

    /**
     * Inicia a campanha (ready | paused | scheduled → running).
     *
     * @throws InterageException
     */
    public function start(string $id): Campaign
    {
        return $this->transition($id, 'start');
    }

    /**
     * Pausa uma campanha em execução (running → paused).
     *
     * @throws InterageException
     */
    public function pause(string $id): Campaign
    {
        return $this->transition($id, 'pause');
    }

    /**
     * Cancela a campanha (→ canceled). Cancelada não reinicia.
     *
     * @throws InterageException
     */
    public function cancel(string $id): Campaign
    {
        return $this->transition($id, 'cancel');
    }

    /**
     * Remove a campanha. Em execução/processamento não pode ser removida.
     *
     * @throws InterageException
     */
    public function delete(string $id): void
    {
        $this->http->delete(self::PATH . '/' . rawurlencode($id));
    }

    /**
     * @throws ApiException
     */
    private function transition(string $id, string $action): Campaign
    {
        return Campaign::fromArray($this->http->patch(self::PATH . '/' . rawurlencode($id) . '/' . $action) ?? []);
    }
}
