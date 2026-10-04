<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage;

use WonitTecnologia\Interage\Exception\ValidationException;
use WonitTecnologia\Interage\Http\CurlTransport;
use WonitTecnologia\Interage\Internal\HttpClient;
use WonitTecnologia\Interage\Resource\Campaigns;
use WonitTecnologia\Interage\Resource\Contacts;
use WonitTecnologia\Interage\Resource\Messages;
use WonitTecnologia\Interage\Resource\Omni;
use WonitTecnologia\Interage\Resource\Telephony;

/**
 * Ponto de entrada do SDK da API pública de clientes da plataforma Interage+ (Wonit).
 *
 *     $cli = new Client('SEU-TENANT.wonit.cloud', 'sk_xxxxxxxx');
 *     $campanhas = $cli->campaigns->list();
 */
final class Client
{
    /** Versão do SDK, enviada no User-Agent (interage-sdk-php/<VERSION>). */
    public const VERSION = '0.1.1';

    /** Campanhas de disparo WhatsApp (criar com CSV, listar, iniciar, pausar, cancelar, remover). */
    public readonly Campaigns $campaigns;
    /** Instâncias, templates HSM e envio de mensagens WhatsApp. */
    public readonly Messages $messages;
    /** Filas, agentes e conversas do atendimento omnichannel. */
    public readonly Omni $omni;
    /** Central de contatos (listar, buscar, criar em lote). */
    public readonly Contacts $contacts;
    /** Ramais, histórico de ligações, click-to-call e gravações. */
    public readonly Telephony $telephony;

    /**
     * @param string       $baseUrl Domínio do seu tenant (ex.: SEU-TENANT.wonit.cloud). Sem scheme,
     *                              o SDK usa https:// (ou http:// com Options::$insecure).
     * @param string       $token   Token de API sk_<valor>, gerado no painel administrativo.
     *                              As permissões de cada rota são configuradas por token.
     * @throws ValidationException
     */
    public function __construct(string $baseUrl, string $token, ?Options $options = null)
    {
        $baseUrl = rtrim(trim($baseUrl), '/');
        if ($baseUrl === '') {
            throw new ValidationException('interage: baseUrl não pode ser vazia');
        }
        if (trim($token) === '') {
            throw new ValidationException('interage: token não pode ser vazio');
        }
        $options ??= new Options();
        if (!preg_match('#^https?://#i', $baseUrl)) {
            $baseUrl = ($options->insecure ? 'http://' : 'https://') . $baseUrl;
        }

        $http = new HttpClient(
            $baseUrl,
            $token,
            $options->transport ?? new CurlTransport(),
            $options->timeout,
            'interage-sdk-php/' . self::VERSION,
        );

        $this->campaigns = new Campaigns($http);
        $this->messages = new Messages($http);
        $this->omni = new Omni($http);
        $this->contacts = new Contacts($http);
        $this->telephony = new Telephony($http);
    }
}
