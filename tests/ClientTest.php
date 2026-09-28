<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Tests;

use WonitTecnologia\Interage\Client;
use WonitTecnologia\Interage\Exception\ApiException;
use WonitTecnologia\Interage\Exception\BadRequestException;
use WonitTecnologia\Interage\Exception\ConflictException;
use WonitTecnologia\Interage\Exception\ForbiddenException;
use WonitTecnologia\Interage\Exception\InterageException;
use WonitTecnologia\Interage\Exception\NotFoundException;
use WonitTecnologia\Interage\Exception\ServerException;
use WonitTecnologia\Interage\Exception\TooManyRequestsException;
use WonitTecnologia\Interage\Exception\UnauthorizedException;
use WonitTecnologia\Interage\Exception\UnexpectedResponseException;
use WonitTecnologia\Interage\Exception\UnprocessableEntityException;
use WonitTecnologia\Interage\Exception\ValidationException;
use WonitTecnologia\Interage\Options;
use WonitTecnologia\Interage\Tests\Support\FakeTransport;
use WonitTecnologia\Interage\Tests\Support\TestCase;

final class ClientTest extends TestCase
{
    public function testHeadersDeAutenticacaoEUserAgent(): void
    {
        $this->transport->respond(['total' => 0, 'items' => []]);
        $this->client->messages->listInstances();

        $request = $this->transport->last();
        self::assertSame('Token sk_teste', $request->headers['Authorization']);
        self::assertSame('interage-sdk-php/' . Client::VERSION, $request->headers['User-Agent']);
        self::assertSame('application/json', $request->headers['Accept']);
        self::assertStringNotContainsString('sk_teste', $request->url, 'o token nunca vai na URL');
    }

    public function testBaseUrlSemSchemeUsaHttps(): void
    {
        $this->transport->respond(['total' => 0, 'items' => []]);
        $this->client->messages->listInstances();
        self::assertStringStartsWith('https://tenant.wonit.cloud/api/public/', $this->transport->last()->url);
    }

    public function testInsecureUsaHttpESchemeExplicitoEhRespeitado(): void
    {
        $transport = (new FakeTransport())->respond(['total' => 0, 'items' => []])->respond(['total' => 0, 'items' => []]);

        (new Client('localhost:8080/', 'sk_x', new Options(insecure: true, transport: $transport)))->messages->listInstances();
        self::assertStringStartsWith('http://localhost:8080/api/public/', $transport->last()->url);

        (new Client('https://outro.wonit.cloud', 'sk_x', new Options(insecure: true, transport: $transport)))->messages->listInstances();
        self::assertStringStartsWith('https://outro.wonit.cloud/api/', $transport->last()->url);
    }

    public function testCredenciaisVaziasSaoRecusadas(): void
    {
        $this->expectException(ValidationException::class);
        new Client('tenant.wonit.cloud', '  ');
    }

    public function testBaseUrlVaziaEhRecusada(): void
    {
        $this->expectException(ValidationException::class);
        new Client('', 'sk_x');
    }

    /**
     * @return iterable<string, array{int, class-string<ApiException>}>
     */
    public static function statusProvider(): iterable
    {
        yield '400' => [400, BadRequestException::class];
        yield '401' => [401, UnauthorizedException::class];
        yield '403' => [403, ForbiddenException::class];
        yield '404' => [404, NotFoundException::class];
        yield '409' => [409, ConflictException::class];
        yield '422' => [422, UnprocessableEntityException::class];
        yield '429' => [429, TooManyRequestsException::class];
        yield '500' => [500, ServerException::class];
        yield '503' => [503, ServerException::class];
        yield '418 (não mapeado)' => [418, ApiException::class];
    }

    /**
     * @dataProvider statusProvider
     * @param class-string<ApiException> $class
     */
    public function testMapeamentoDeErros(int $status, string $class): void
    {
        $this->transport->respondRaw($status, json_encode(['code' => $status, 'status' => 'X_ERR', 'message' => 'falhou']));
        try {
            $this->client->campaigns->get('abc');
            self::fail('esperava exceção');
        } catch (ApiException $e) {
            self::assertSame($class, $e::class);
            self::assertInstanceOf(InterageException::class, $e);
            self::assertSame($status, $e->statusCode);
            self::assertSame('X_ERR', $e->apiStatus);
            self::assertSame('falhou', $e->apiMessage);
        }
    }

    public function testLimiteDeRequisicoesInformaRetryAfter(): void
    {
        $this->transport->respondRaw(429, '{"code":429,"status":"TOO_MANY_REQUESTS","message":"Limite excedido"}', ['Retry-After' => '12']);
        try {
            $this->client->omni->listAgents();
            self::fail('esperava TooManyRequestsException');
        } catch (TooManyRequestsException $e) {
            self::assertSame(12, $e->retryAfter);
        }
    }

    public function testErroComCorpoNaoJsonPreservaOTexto(): void
    {
        $this->transport->respondRaw(502, '<html>Bad Gateway</html>');
        try {
            $this->client->campaigns->get('abc');
            self::fail('esperava ServerException');
        } catch (ServerException $e) {
            self::assertSame('HTTP_502', $e->apiStatus);
            self::assertSame('<html>Bad Gateway</html>', $e->apiMessage);
            self::assertNull($e->retryAfter);
        }
    }

    public function testSucessoComCorpoInvalidoLancaUnexpectedResponse(): void
    {
        $this->transport->respondRaw(200, 'isto não é json');
        $this->expectException(UnexpectedResponseException::class);
        $this->client->campaigns->get('abc');
    }
}
