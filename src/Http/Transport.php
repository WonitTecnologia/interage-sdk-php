<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Http;

use WonitTecnologia\Interage\Exception\TransportException;

/**
 * Executa a requisição HTTP. O padrão é {@see CurlTransport}; implemente esta
 * interface para usar outro cliente (proxy, cliente HTTP do seu framework) ou
 * para simular a API em testes.
 */
interface Transport
{
    /**
     * Deve devolver a resposta para QUALQUER status HTTP (inclusive 4xx/5xx) — o
     * tratamento de erro da API é feito pelo SDK. Só falhas de rede/conexão
     * lançam exceção.
     *
     * @throws TransportException
     */
    public function send(Request $request): Response;
}
