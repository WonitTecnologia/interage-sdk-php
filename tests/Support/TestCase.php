<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Tests\Support;

use WonitTecnologia\Interage\Client;
use WonitTecnologia\Interage\Options;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected FakeTransport $transport;
    protected Client $client;

    protected function setUp(): void
    {
        $this->transport = new FakeTransport();
        $this->client = new Client('tenant.wonit.cloud', 'sk_teste', new Options(transport: $this->transport));
    }
}
