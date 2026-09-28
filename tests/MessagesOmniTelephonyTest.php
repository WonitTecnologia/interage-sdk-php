<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Tests;

use WonitTecnologia\Interage\Enum\ConversationStatus;
use WonitTecnologia\Interage\Exception\ValidationException;
use WonitTecnologia\Interage\Request\OriginateCallRequest;
use WonitTecnologia\Interage\Request\SendMessageRequest;
use WonitTecnologia\Interage\Request\SendTemplateRequest;
use WonitTecnologia\Interage\Request\TransferConversationRequest;
use WonitTecnologia\Interage\Tests\Support\TestCase;

final class MessagesOmniTelephonyTest extends TestCase
{
    // ── Messages ──────────────────────────────────────────────────────────────

    public function testSendTemplateOmiteOpcionaisNaoInformados(): void
    {
        $this->transport->respond(['message_id' => 'wamid.1', 'internal_message_id' => 42, 'conversation_id' => null]);

        $result = $this->client->messages->sendTemplate(new SendTemplateRequest(
            to: '5547999999999',
            templateId: 'tpl-1',
            instanceId: 'inst-1',
            params: ['Maria'],
        ));

        self::assertSame('/api/public/whatsapp/messages/templates/send', $this->transport->lastPath());
        self::assertSame(
            ['to' => '5547999999999', 'template_id' => 'tpl-1', 'instance_id' => 'inst-1', 'params' => ['Maria']],
            $this->transport->lastJson(),
        );
        self::assertSame(42, $result->internalMessageId);
        self::assertNull($result->conversationId);
    }

    public function testSendTemplateValidaObrigatorios(): void
    {
        $this->expectException(ValidationException::class);
        $this->client->messages->sendTemplate(new SendTemplateRequest('', 'tpl', 'inst'));
    }

    public function testSendMessageConstrutoresNomeados(): void
    {
        $this->transport->respond(['message_id' => 'm', 'internal_message_id' => 1]);
        $this->client->messages->sendMessage(SendMessageRequest::text('PROT-1', 'Olá'));
        self::assertSame(['protocol' => 'PROT-1', 'type' => 'text', 'text' => 'Olá'], $this->transport->lastJson());

        $this->transport->respond(['message_id' => 'm', 'internal_message_id' => 2]);
        $this->client->messages->sendMessage(SendMessageRequest::location('PROT-1', -26.9, -48.6, 'Loja'));
        self::assertSame(
            ['protocol' => 'PROT-1', 'type' => 'location', 'latitude' => -26.9, 'longitude' => -48.6, 'location_name' => 'Loja'],
            $this->transport->lastJson(),
        );
    }

    public function testGetMessageStatus(): void
    {
        $this->transport->respond(['id' => 42, 'status' => 'delivered', 'delivered_at' => '2026-09-28T10:00:00Z', 'error_code' => null]);
        $status = $this->client->messages->getMessageStatus(42);
        self::assertSame(['message_id' => '42'], $this->transport->lastQuery());
        self::assertNotNull($status->deliveredAt);
        self::assertNull($status->readAt);
        self::assertNull($status->errorCode);
    }

    public function testListTemplatesUsaNomesDeFiltroDaApi(): void
    {
        $this->transport->respond(['total' => 0, 'items' => []]);
        $this->client->messages->listTemplates(status: 'APPROVED', type: 'IMAGE', instanceId: 'inst-1', page: 2);
        self::assertSame(['status' => 'APPROVED', 'type' => 'IMAGE', 'instance_id' => 'inst-1', 'page' => '2'], $this->transport->lastQuery());
    }

    // ── Omni ──────────────────────────────────────────────────────────────────

    public function testListConversationsAceitaEnum(): void
    {
        $this->transport->respond(['total' => 0, 'items' => []]);
        $this->client->omni->listConversations(status: ConversationStatus::Attending, channelType: 'instagram');
        self::assertSame(['status' => 'attending', 'channel_type' => 'instagram'], $this->transport->lastQuery());
    }

    public function testTransferParaFila(): void
    {
        $this->transport->respond(['id' => 7, 'protocol' => 'P-1', 'status' => 'queue', 'target_type' => 'queue', 'queue_id' => 3, 'user_id' => null]);
        $result = $this->client->omni->transferConversation('P-1', TransferConversationRequest::toQueue(3));

        self::assertSame('/api/public/omni/administrativo/conversations/P-1/transfer', $this->transport->lastPath());
        self::assertSame(['target_type' => 'queue', 'queue_id' => 3], $this->transport->lastJson());
        self::assertSame(3, $result->queueId);
        self::assertNull($result->userId);
    }

    public function testHistoricoDaConversa(): void
    {
        $this->transport->respond([
            'conversation' => ['id' => 7, 'protocol' => 'P-1', 'status' => 'finished', 'contact_phone' => '5547', 'orientation' => 'inbound', 'created_at' => '2026-09-28T10:00:00Z'],
            'total' => 1,
            'page' => 1,
            'page_size' => 20,
            'items' => [['id' => 99, 'direction' => 'inbound', 'message_type' => 'image', 'media_url' => '/bucket/v2/chat/files/x', 'status' => 'read', 'created_at' => '2026-09-28T10:00:01Z']],
        ]);
        $history = $this->client->omni->getConversationHistory('P-1', pageSize: 20);

        self::assertSame('P-1', $history->conversation->protocol);
        self::assertSame(99, $history->items[0]->id);
        self::assertNull($history->items[0]->content);
    }

    public function testLinkTemporarioDeArquivoDeMensagem(): void
    {
        $this->transport->respond(['token' => 't', 'url' => 'https://x/bucket/v2/public/t', 'expires_at' => '2026-09-28T11:00:00Z', 'expires_in' => 3600], 201);
        $link = $this->client->omni->createMessageFileTempLink(99, 3600);

        self::assertSame('POST', $this->transport->last()->method);
        self::assertSame('/api/public/omni/administrativo/conversations/messages/99/templink', $this->transport->lastPath());
        self::assertSame(['expires_in' => '3600'], $this->transport->lastQuery());
        self::assertNull($this->transport->last()->body);
        self::assertSame(3600, $link->expiresIn);
    }

    // ── Telephony ─────────────────────────────────────────────────────────────

    public function testHistoricoDeLigacoesFormataDatasEAceitaCursor(): void
    {
        $this->transport->respond(['total' => 1, 'next_cursor' => 'prox', 'items' => [[
            'call_id' => 'c1', 'unique_id' => 'u1', 'status' => 'ANSWERED', 'start_time' => '2026-09-01T10:00:00Z',
            'duration' => null, 'recording_enabled' => true, 'is_abandoned' => false,
        ]]]);

        $page = $this->client->telephony->listCallHistory(
            dateFrom: new \DateTimeImmutable('2026-09-01 08:00'),
            dateTo: new \DateTimeImmutable('2026-09-28 18:00'),
            callResult: 'ANSWERED',
            cursor: 'cur',
        );

        self::assertSame(['date_from' => '2026-09-01', 'date_to' => '2026-09-28', 'call_result' => 'ANSWERED', 'cursor' => 'cur'], $this->transport->lastQuery());
        self::assertSame('prox', $page->nextCursor);
        self::assertNull($page->items[0]->duration, 'duração nula continua null, não 0');
        self::assertTrue($page->items[0]->recordingEnabled);
    }

    public function testHistoricoRecusaPeriodoInvertido(): void
    {
        $this->expectException(ValidationException::class);
        $this->client->telephony->listCallHistory(new \DateTimeImmutable('2026-09-28'), new \DateTimeImmutable('2026-09-01'));
    }

    public function testClickToCall(): void
    {
        $this->transport->respond(['action_id' => 'a1', 'channel' => 'PJSIP/1000', 'extension' => '1000', 'to_number' => '5547']);
        $result = $this->client->telephony->originateCall(new OriginateCallRequest('1000', '5547999999999'));
        self::assertSame(['from_extension' => '1000', 'to_number' => '5547999999999'], $this->transport->lastJson());
        self::assertSame('a1', $result->actionId);
    }

    public function testChamadasAtivas(): void
    {
        $this->transport->respond(['total' => 1, 'items' => [['call_id' => 'c1', 'unique_id' => 'u1', 'channel' => 'PJSIP/1000', 'status' => 'up', 'start_time' => '2026-09-28T10:00:00Z', 'duration' => 30]]]);
        $list = $this->client->telephony->listActiveCalls();
        self::assertSame(1, $list->total);
        self::assertSame(30, $list->items[0]->duration);
    }

    public function testLinkDaGravacao(): void
    {
        $this->transport->respond(['token' => 't', 'url' => 'u', 'expires_at' => '2026-09-28T11:00:00Z', 'expires_in' => 60], 201);
        $this->client->telephony->createRecordingTempLink('1727000000.123');
        self::assertSame('/api/public/pabx/telefonia/historico/1727000000.123/recording/templink', $this->transport->lastPath());
        self::assertSame([], $this->transport->lastQuery(), 'expires_in não informado não vai na query');
    }
}
