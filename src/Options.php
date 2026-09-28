<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage;

use WonitTecnologia\Interage\Http\Transport;

/**
 * Opções adicionais do cliente. Todos os campos são opcionais.
 */
final class Options
{
    /**
     * @param float          $timeout   Tempo máximo de cada requisição, em segundos.
     * @param bool           $insecure  Força http:// (sem TLS) quando a baseUrl vem sem scheme.
     *                                  Útil só para desenvolvimento local.
     * @param Transport|null $transport Transporte HTTP próprio (proxy, cliente do seu framework,
     *                                  testes). Padrão: CurlTransport.
     */
    public function __construct(
        public readonly float $timeout = 30.0,
        public readonly bool $insecure = false,
        public readonly ?Transport $transport = null,
    ) {
    }
}
