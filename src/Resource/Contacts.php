<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Resource;

use WonitTecnologia\Interage\Exception\InterageException;
use WonitTecnologia\Interage\Exception\NotFoundException;
use WonitTecnologia\Interage\Internal\HttpClient;
use WonitTecnologia\Interage\Model\BatchCreateContactsResult;
use WonitTecnologia\Interage\Model\Contact;
use WonitTecnologia\Interage\Model\Page;
use WonitTecnologia\Interage\Request\BatchCreateContactsRequest;

/**
 * Central de contatos do tenant.
 */
final class Contacts
{
    private const PATH = '/api/public/omni/administrativo/contacts';

    /** @internal Obtenha pelo Client: $cli->contacts. */
    public function __construct(private readonly HttpClient $http)
    {
    }

    /**
     * Lista os contatos.
     *
     * @param string|null $search Busca geral: nome, empresa, identidades e campos personalizados.
     * @param string|null $name   Filtro por nome/empresa (ignorado se $search for informado).
     * @param string|null $cursor Page::$nextCursor da página anterior (ignora $page).
     * @return Page<Contact>
     * @throws InterageException
     */
    public function list(
        ?string $search = null,
        ?string $name = null,
        ?int $page = null,
        ?int $pageSize = null,
        ?string $cursor = null,
    ): Page {
        $data = $this->http->get(self::PATH, [
            'search' => $search,
            'name' => $name,
            'page' => $page,
            'page_size' => $pageSize,
            'cursor' => $cursor,
        ]);
        return Page::fromArray($data ?? [], static fn (array $i): Contact => Contact::fromArray($i));
    }

    /**
     * @throws InterageException
     */
    public function get(string $id): Contact
    {
        return Contact::fromArray($this->http->get(self::PATH . '/' . rawurlencode($id)) ?? []);
    }

    /**
     * Busca pelo telefone (normalizado para dígitos) nos canais phone, whatsapp e sms.
     *
     * @throws NotFoundException quando não existe contato com o número.
     * @throws InterageException
     */
    public function getByPhone(string $phone): Contact
    {
        return Contact::fromArray($this->http->get(self::PATH . '/by-phone', ['phone' => $phone]) ?? []);
    }

    /**
     * Cria até 500 contatos de uma vez. Cada item é processado de forma independente —
     * o erro de um não impede os demais (ver BatchCreateContactsResult::$items).
     *
     * @throws InterageException
     */
    public function batchCreate(BatchCreateContactsRequest $request): BatchCreateContactsResult
    {
        return BatchCreateContactsResult::fromArray($this->http->post(self::PATH . '/batch', $request->toArray()) ?? []);
    }
}
