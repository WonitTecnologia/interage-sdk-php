<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Tests;

use WonitTecnologia\Interage\Enum\CollisionPolicy;
use WonitTecnologia\Interage\Exception\ValidationException;
use WonitTecnologia\Interage\Request\BatchContactItem;
use WonitTecnologia\Interage\Request\BatchCreateContactsRequest;
use WonitTecnologia\Interage\Request\ContactIdentityInput;
use WonitTecnologia\Interage\Tests\Support\TestCase;

final class ContactsTest extends TestCase
{
    private static function item(array $labels = [1, 3], array $customInfo = ['codigo_cliente' => 'C-123']): BatchContactItem
    {
        return new BatchContactItem(
            name: 'Maria',
            identities: [ContactIdentityInput::whatsapp('5511999998888', isPrimary: true)],
            email: 'maria@empresa.com',
            customInfo: $customInfo,
            labels: $labels,
        );
    }

    // A API espera labels como números (tenant_seq) e custom_info só com texto.
    public function testBatchCreateEnviaLabelsComoNumerosECustomInfoComoObjeto(): void
    {
        $this->transport->respond(['total' => 1, 'created' => 1, 'items' => [['index' => 0, 'status' => 'created', 'contact_id' => 'ct-1']]]);

        $result = $this->client->contacts->batchCreate(new BatchCreateContactsRequest([self::item()], CollisionPolicy::Overwrite));

        self::assertSame('/api/public/omni/administrativo/contacts/batch', $this->transport->lastPath());
        $body = (string) $this->transport->last()->body;
        self::assertStringContainsString('"labels":[1,3]', $body);
        self::assertStringContainsString('"custom_info":{"codigo_cliente":"C-123"}', $body);
        self::assertSame([
            'collision_policy' => 'overwrite',
            'contacts' => [[
                'name' => 'Maria',
                'identities' => [['channel' => 'whatsapp', 'id_type' => 'phone', 'id_value' => '5511999998888', 'is_primary' => true]],
                'email' => 'maria@empresa.com',
                'custom_info' => ['codigo_cliente' => 'C-123'],
                'labels' => [1, 3],
            ]],
        ], $this->transport->lastJson());

        self::assertSame(1, $result->created);
        self::assertSame('ct-1', $result->items[0]->contactId);
        self::assertNull($result->items[0]->message);
    }

    public function testCustomInfoComChavesNumericasContinuaObjeto(): void
    {
        $this->transport->respond(['total' => 1, 'items' => []]);
        $this->client->contacts->batchCreate(new BatchCreateContactsRequest([self::item([], ['0' => 'a'])]));
        self::assertStringContainsString('"custom_info":{"0":"a"}', (string) $this->transport->last()->body);
    }

    /**
     * @return iterable<string, array{BatchCreateContactsRequest}>
     */
    public static function invalidProvider(): iterable
    {
        yield 'etiqueta por nome' => [new BatchCreateContactsRequest([self::item(['vip'])])];
        yield 'custom_info não-texto' => [new BatchCreateContactsRequest([self::item([], ['idade' => 30])])];
        yield 'sem identidades' => [new BatchCreateContactsRequest([new BatchContactItem('Maria', [])])];
        yield 'mais de 20 etiquetas' => [new BatchCreateContactsRequest([self::item(range(1, 21))])];
        yield 'lote vazio' => [new BatchCreateContactsRequest([])];
        yield 'mais de 500' => [new BatchCreateContactsRequest(array_fill(0, 501, self::item()))];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testBatchCreateValidaAntesDeChamarAApi(BatchCreateContactsRequest $request): void
    {
        try {
            $this->client->contacts->batchCreate($request);
            self::fail('esperava ValidationException');
        } catch (ValidationException) {
            self::assertSame([], $this->transport->requests, 'nada é enviado quando a validação falha');
        }
    }

    public function testListComCursor(): void
    {
        $this->transport->respond(['total' => 2, 'next_cursor' => 'prox', 'items' => [[
            'id' => 'ct-1',
            'name' => 'Maria',
            'cpf' => null,
            'custom_info' => ['plano' => 'premium'],
            'identities' => [['channel' => 'whatsapp', 'id_type' => 'phone', 'id_value' => '5511', 'is_primary' => true]],
            'created_at' => '2026-09-28T10:00:00Z',
            'updated_at' => '2026-09-28T10:00:00Z',
        ]]]);

        $page = $this->client->contacts->list(search: 'maria', pageSize: 100, cursor: 'cur');

        self::assertSame(['search' => 'maria', 'page_size' => '100', 'cursor' => 'cur'], $this->transport->lastQuery());
        self::assertSame('prox', $page->nextCursor);
        $contact = $page->items[0];
        self::assertNull($contact->cpf);
        self::assertSame(['plano' => 'premium'], $contact->customInfo);
        self::assertTrue($contact->identities[0]->isPrimary);
    }

    public function testGetByPhone(): void
    {
        $this->transport->respond(['id' => 'ct-1', 'name' => 'Maria', 'created_at' => '2026-09-28T10:00:00Z', 'updated_at' => '2026-09-28T10:00:00Z']);
        $this->client->contacts->getByPhone('+55 (47) 99999-9999');
        self::assertSame('/api/public/omni/administrativo/contacts/by-phone', $this->transport->lastPath());
        self::assertSame(['phone' => '+55 (47) 99999-9999'], $this->transport->lastQuery());
    }
}
