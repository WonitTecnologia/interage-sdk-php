<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Tests;

use WonitTecnologia\Interage\Enum\CampaignStatus;
use WonitTecnologia\Interage\Enum\CollisionPolicy;
use WonitTecnologia\Interage\Exception\ValidationException;
use WonitTecnologia\Interage\Request\CreateCampaignRequest;
use WonitTecnologia\Interage\Tests\Support\TestCase;

final class CampaignsTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function campaign(): array
    {
        return [
            'id' => 'c1',
            'name' => 'Black Friday',
            'description' => null,
            'channel' => 'whatsapp',
            'status' => 'running',
            'template_id' => 'tpl-1',
            'start_at' => '2026-09-28T09:00:00.123456789-03:00',
            'total_contacts' => 100,
            'sent_count' => 40,
            'replied_count' => 3,
            'reply_without_context' => true,
            'reply_window_hours' => 48,
            'created_at' => '2026-09-27T10:00:00Z',
            'updated_at' => '2026-09-28T10:00:00Z',
        ];
    }

    public function testCreateEnviaMultipartComCamposEArquivo(): void
    {
        $this->transport->respond(['campaign_id' => 'c1', 'status' => 'pending', 'mailing_id' => 'm1', 'file_name' => 'contatos.csv'], 201);

        $result = $this->client->campaigns->create(new CreateCampaignRequest(
            name: 'Black Friday',
            instanceId: 'inst-1',
            templateId: 'tpl-1',
            collisionPolicy: CollisionPolicy::UpdateEmpty,
            fileName: 'contatos.csv',
            fileContent: "phone;name\n5547999999999;Maria\n",
            templateParams: ['Maria', '10%'],
            startAt: new \DateTimeImmutable('2026-10-01T09:00:00-03:00'),
            autoStart: true,
            settings: ['delay_ms' => 1000],
        ));

        self::assertSame('c1', $result->campaignId);
        $request = $this->transport->last();
        self::assertSame('POST', $request->method);
        self::assertSame('/api/public/whatsapp/campanhas', $this->transport->lastPath());
        self::assertMatchesRegularExpression('#^multipart/form-data; boundary=(.+)$#', $request->headers['Content-Type']);

        $body = (string) $request->body;
        foreach ([
            'name' => 'Black Friday',
            'channel' => 'whatsapp',
            'instance_id' => 'inst-1',
            'template_id' => 'tpl-1',
            'collision_policy' => 'update_empty',
            'template_params' => '["Maria","10%"]',
            'start_at' => '2026-10-01T09:00:00-03:00',
            'auto_start' => 'true',
            'settings' => '{"delay_ms":1000}',
        ] as $field => $value) {
            self::assertStringContainsString("name=\"$field\"\r\n\r\n$value\r\n", $body, "campo $field");
        }
        self::assertStringContainsString('name="file"; filename="contatos.csv"', $body);
        self::assertStringContainsString("5547999999999;Maria", $body);
    }

    public function testCreateValidaCamposObrigatorios(): void
    {
        $this->expectException(ValidationException::class);
        $this->client->campaigns->create(new CreateCampaignRequest('', 'i', 't', CollisionPolicy::Ignore, 'a.csv', 'x'));
    }

    public function testListComFiltrosECursor(): void
    {
        $this->transport->respond(['total' => 30, 'page' => 1, 'page_size' => 10, 'next_cursor' => 'prox-123', 'items' => [self::campaign()]]);

        $page = $this->client->campaigns->list(search: 'black', status: CampaignStatus::Running, pageSize: 10, cursor: 'cur-abc');

        self::assertSame(['search' => 'black', 'status' => 'running', 'page_size' => '10', 'cursor' => 'cur-abc'], $this->transport->lastQuery());
        self::assertSame('prox-123', $page->nextCursor);
        self::assertSame(30, $page->total);
        self::assertCount(1, $page->items);
        self::assertSame('Black Friday', $page->items[0]->name);
    }

    public function testUltimaPaginaNaoTemCursor(): void
    {
        $this->transport->respond(['total' => 1, 'next_cursor' => '', 'items' => []]);
        self::assertNull($this->client->campaigns->list()->nextCursor);
        self::assertSame([], $this->transport->lastQuery(), 'filtros não informados não vão na query');
    }

    public function testGetConverteTiposEDatas(): void
    {
        $this->transport->respond(self::campaign());
        $c = $this->client->campaigns->get('c/1');

        self::assertSame('/api/public/whatsapp/campanhas/c%2F1', $this->transport->lastPath());
        self::assertNull($c->description, 'null da API continua null');
        self::assertNull($c->endAt);
        self::assertSame(CampaignStatus::Running, CampaignStatus::tryFrom($c->status));
        self::assertTrue($c->replyWithoutContext);
        self::assertSame(48, $c->replyWindowHours);
        self::assertSame('2026-09-28T09:00:00.123456-03:00', $c->startAt?->format('Y-m-d\TH:i:s.uP'), 'nanossegundos truncados para micro');
    }

    public function testTransicoesUsamPatch(): void
    {
        foreach (['start', 'pause', 'cancel'] as $action) {
            $this->transport->respond(self::campaign());
            $this->client->campaigns->{$action}('c1');
            self::assertSame('PATCH', $this->transport->last()->method);
            self::assertSame("/api/public/whatsapp/campanhas/c1/$action", $this->transport->lastPath());
        }
    }

    public function testDelete(): void
    {
        $this->transport->respond(null);
        $this->client->campaigns->delete('c1');
        self::assertSame('DELETE', $this->transport->last()->method);
        self::assertNull($this->transport->last()->body);
    }
}
