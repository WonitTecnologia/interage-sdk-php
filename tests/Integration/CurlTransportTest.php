<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Tests\Integration;

use PHPUnit\Framework\TestCase;
use WonitTecnologia\Interage\Exception\TooManyRequestsException;
use WonitTecnologia\Interage\Exception\TransportException;
use WonitTecnologia\Interage\Http\CurlTransport;
use WonitTecnologia\Interage\Http\Request;
use WonitTecnologia\Interage\Internal\HttpClient;

/**
 * CurlTransport contra um servidor HTTP real (php -S) — valida headers, query,
 * JSON e o multipart montado pelo SDK com o parser do próprio PHP.
 */
final class CurlTransportTest extends TestCase
{
    /** @var resource|null */
    private static $server = null;
    private static int $port = 0;

    public static function setUpBeforeClass(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        self::assertIsResource($socket);
        self::$port = (int) substr((string) stream_socket_get_name($socket, false), strrpos((string) stream_socket_get_name($socket, false), ':') + 1);
        fclose($socket);

        $cmd = [PHP_BINARY, '-S', '127.0.0.1:' . self::$port, __DIR__ . '/router.php'];
        $process = proc_open($cmd, [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes);
        self::assertIsResource($process);
        self::$server = $process;

        for ($i = 0; $i < 50; $i++) {
            $conn = @fsockopen('127.0.0.1', self::$port);
            if ($conn !== false) {
                fclose($conn);
                return;
            }
            usleep(100_000);
        }
        self::fail('servidor de teste não subiu');
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$server !== null) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
    }

    private function http(): HttpClient
    {
        return new HttpClient('http://127.0.0.1:' . self::$port, 'sk_real', new CurlTransport(), 5.0, 'interage-sdk-php/teste');
    }

    public function testGetComQueryEHeaders(): void
    {
        $data = $this->http()->get('/api/public/eco', ['search' => 'joão silva', 'page' => 2, 'vazio' => null]);

        self::assertSame('GET', $data['method']);
        self::assertSame('/api/public/eco', $data['path']);
        self::assertSame(['search' => 'joão silva', 'page' => '2'], $data['query']);
        self::assertSame('Token sk_real', $data['authorization']);
        self::assertSame('interage-sdk-php/teste', $data['user_agent']);
    }

    public function testPostJson(): void
    {
        $data = $this->http()->post('/api/public/eco', ['labels' => [1, 3], 'nome' => 'Açaí']);
        self::assertSame('POST', $data['method']);
        self::assertSame(['labels' => [1, 3], 'nome' => 'Açaí'], $data['json']);
    }

    public function testMultipartEhLidoPeloParserDoPhp(): void
    {
        $csv = "phone;name\n5547999999999;José \"Zé\"\n";
        $data = $this->http()->postMultipart(
            '/api/public/eco',
            ['name' => 'Campanha "A"', 'template_params' => '["x"]'],
            'file',
            'contatos.csv',
            $csv,
            'text/csv',
        );

        self::assertSame(['name' => 'Campanha "A"', 'template_params' => '["x"]'], $data['post']);
        self::assertSame('contatos.csv', $data['files']['file']['name']);
        self::assertSame($csv, $data['files']['file']['content']);
    }

    public function testPatchEDeleteSemCorpo(): void
    {
        self::assertSame('PATCH', $this->http()->patch('/api/public/eco')['method']);
        self::assertSame('DELETE', $this->http()->delete('/api/public/eco')['method']);
    }

    public function testErroHttpComRetryAfter(): void
    {
        try {
            $this->http()->get('/api/public/limite');
            self::fail('esperava TooManyRequestsException');
        } catch (TooManyRequestsException $e) {
            self::assertSame(429, $e->statusCode);
            self::assertSame(7, $e->retryAfter);
            self::assertSame('Limite excedido', $e->apiMessage);
        }
    }

    public function testFalhaDeConexaoViraTransportException(): void
    {
        $this->expectException(TransportException::class);
        (new CurlTransport())->send(new Request('GET', 'http://127.0.0.1:1/nada', [], null, 2.0));
    }
}
