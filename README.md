# interage-sdk-php

SDK PHP oficial da **API pública de clientes** da plataforma **Interage+** (Wonit).

- PHP **8.1+**, sem dependências — usa só as extensões `curl` e `json`.
- Respostas **tipadas**: cada chamada devolve objetos com propriedades `readonly`,
  datas como `DateTimeImmutable` e campos ausentes como `null`.
- Mesmo contrato do [SDK Go](https://github.com/WonitTecnologia/interage-sdk-go).

---

## Instalação

```bash
composer require wonittecnologia/interage-sdk-php
```

## Início rápido

```php
<?php

require 'vendor/autoload.php';

use WonitTecnologia\Interage\Client;

// Domínio do seu tenant (https:// é adicionado automaticamente).
$cli = new Client('SEU-TENANT.wonit.cloud', 'sk_xxxxxxxxxxxx');

$campanhas = $cli->campaigns->list();
foreach ($campanhas->items as $c) {
    echo $c->name, ' — ', $c->status, ' — ', $c->totalContacts, PHP_EOL;
}
```

### Configuração

`new Client(string $baseUrl, string $token, ?Options $options = null)`

| Parâmetro | Obrigatório | Descrição |
|---|---|---|
| `$baseUrl` | sim | Domínio do tenant (ex.: `SEU-TENANT.wonit.cloud`). Sem scheme, o SDK usa `https://`. |
| `$token` | sim | Token de API `sk_<valor>` (painel administrativo → Tokens de API). |
| `Options::$timeout` | não | Tempo máximo de cada requisição, em segundos (padrão: 30). |
| `Options::$insecure` | não | Usa `http://` quando a `baseUrl` vem sem scheme. Só para desenvolvimento local. |
| `Options::$transport` | não | Transporte HTTP próprio (proxy, cliente HTTP do seu framework, testes). Padrão: curl. |

```php
use WonitTecnologia\Interage\Options;

$cli = new Client('SEU-TENANT.wonit.cloud', 'sk_xxx', new Options(timeout: 10));
```

> As permissões de cada rota (leitura, listagem, criação, alteração, remoção) são
> configuradas **por token** no painel. Token ausente, inválido, expirado ou revogado
> responde **401**; token válido sem a rota ou sem a ação responde **403**.

## Domínios

| Propriedade | Cobre |
|---|---|
| `$cli->campaigns` | Campanhas de disparo WhatsApp |
| `$cli->messages` | Instâncias, templates HSM e envio de mensagens |
| `$cli->omni` | Filas, agentes e conversas |
| `$cli->contacts` | Central de contatos |
| `$cli->telephony` | Ramais, histórico de ligações, click-to-call e gravações |

---

## Campaigns — campanhas de disparo WhatsApp

### Criar campanha com CSV

O CSV deve ter delimitador `,` ou `;` e uma coluna de telefone (`phone`, `telefone`,
`numero`, `celular` ou `whatsapp`). Colunas opcionais: `name`, `email`, `company`.

```php
use WonitTecnologia\Interage\Enum\CollisionPolicy;
use WonitTecnologia\Interage\Request\CreateCampaignRequest;

$criada = $cli->campaigns->create(new CreateCampaignRequest(
    name: 'Black Friday',
    instanceId: '<instance_id>',   // $cli->messages->listInstances()
    templateId: '<gupshup_id>',     // $cli->messages->listTemplates()
    collisionPolicy: CollisionPolicy::UpdateEmpty,
    fileName: 'contatos.csv',
    fileContent: file_get_contents('contatos.csv'),
    templateParams: ['10%'],
    startAt: new DateTimeImmutable('2026-11-27 09:00'),
    autoStart: true,
));
// A importação é assíncrona: nasce "pending" e passa a "ready" ao terminar.
echo $criada->campaignId;
```

### Listar, detalhar e controlar

```php
use WonitTecnologia\Interage\Enum\CampaignStatus;

$pagina = $cli->campaigns->list(status: CampaignStatus::Running, pageSize: 50);
$campanha = $cli->campaigns->get($id);

$cli->campaigns->start($id);   // ready | paused | scheduled → running
$cli->campaigns->pause($id);   // running → paused
$cli->campaigns->cancel($id);  // → canceled (não reinicia)
$cli->campaigns->delete($id);  // não remove em execução/processamento

if (CampaignStatus::tryFrom($campanha->status) === CampaignStatus::Completed) { /* ... */ }
```

---

## Messages — instâncias, templates e envio

```php
use WonitTecnologia\Interage\Request\SendMessageRequest;
use WonitTecnologia\Interage\Request\SendTemplateRequest;

$instancias = $cli->messages->listInstances();
$templates = $cli->messages->listTemplates(status: 'APPROVED', instanceId: '<instance_id>');

// Template HSM (inicia conversa)
$envio = $cli->messages->sendTemplate(new SendTemplateRequest(
    to: '5547999999999',
    templateId: '<gupshup_id>',
    instanceId: '<instance_id>',
    params: ['Maria'],
));

// Mensagem livre, dentro da sessão de 24h de uma conversa ativa
$cli->messages->sendMessage(SendMessageRequest::text('<protocolo>', 'Olá!'));
$cli->messages->sendMessage(SendMessageRequest::media('<protocolo>', 'image', 'https://.../foto.jpg', caption: 'Veja'));
$cli->messages->sendMessage(SendMessageRequest::location('<protocolo>', -26.91, -48.66, 'Loja Centro'));

// Status de entrega
$status = $cli->messages->getMessageStatus($envio->internalMessageId);
echo $status->status, ' ', $status->deliveredAt?->format('d/m H:i');
```

---

## Omni — filas, agentes e conversas

```php
use WonitTecnologia\Interage\Enum\ConversationStatus;
use WonitTecnologia\Interage\Request\TransferConversationRequest;

$filas = $cli->omni->listQueues();
$agentes = $cli->omni->listAgents();
$conversas = $cli->omni->listConversations(status: ConversationStatus::Queue, channelType: 'whatsapp');

$historico = $cli->omni->getConversationHistory('<protocolo>', pageSize: 50);
$cli->omni->transferConversation('<protocolo>', TransferConversationRequest::toQueue(3));
$cli->omni->transferConversation('<protocolo>', TransferConversationRequest::toUser('<user_id>'));
$cli->omni->closeConversation('<protocolo>');

// Arquivo de uma mensagem do histórico (id da mensagem)
$link = $cli->omni->createMessageFileTempLink($historico->items[0]->id, 3600);
echo $link->url;
```

---

## Contacts — central de contatos

```php
$pagina = $cli->contacts->list(search: 'joão', pageSize: 20);
$contato = $cli->contacts->get('<contact_uuid>');
$contato = $cli->contacts->getByPhone('5547999999999'); // NotFoundException se não existir
```

### Criar contatos em lote

Até 500 contatos por chamada. Cada item é processado de forma independente — o erro
de um não impede os demais.

```php
use WonitTecnologia\Interage\Enum\CollisionPolicy;
use WonitTecnologia\Interage\Request\BatchContactItem;
use WonitTecnologia\Interage\Request\BatchCreateContactsRequest;
use WonitTecnologia\Interage\Request\ContactIdentityInput;

$resultado = $cli->contacts->batchCreate(new BatchCreateContactsRequest(
    contacts: [
        new BatchContactItem(
            name: 'João da Silva',
            identities: [ContactIdentityInput::whatsapp('5511999998888', isPrimary: true)],
            email: 'joao@empresa.com',
            customInfo: ['codigo_cliente' => 'C-123'],
            labels: [1, 3], // números das etiquetas, não os nomes
        ),
    ],
    collisionPolicy: CollisionPolicy::Ignore,
));

foreach ($resultado->items as $item) {
    if ($item->status === 'error') {
        echo "Contato #{$item->index}: {$item->message}", PHP_EOL;
    }
}
```

- `labels` recebe o **número** de cada etiqueta — o número sequencial exibido em
  Admin → Contatos → Etiquetas. A etiqueta precisa existir e estar ativa; senão o item
  falha e nada é gravado para aquele contato.
- `customInfo` aceita só texto: números e datas vão como string (`'1990-05-20'`).
- Limites por contato: nome 255 caracteres, CPF 14, e-mail/empresa 255, até 10
  identidades, até 20 etiquetas, `customInfo` até 10 KB.

---

## Telephony — ramais, histórico e click-to-call

```php
use WonitTecnologia\Interage\Request\OriginateCallRequest;

$ramais = $cli->telephony->listExtensions(search: '10');

$historico = $cli->telephony->listCallHistory(
    dateFrom: new DateTimeImmutable('2026-09-01'),
    dateTo: new DateTimeImmutable('2026-09-30'), // período de no máximo 3 meses
    callResult: 'ANSWERED',
);

$ativas = $cli->telephony->listActiveCalls();

$chamada = $cli->telephony->originateCall(new OriginateCallRequest('1000', '5547999999999'));

$gravacao = $cli->telephony->createRecordingTempLink('<call_id>', 3600);
```

---

## Paginação

As listagens devolvem um `Page` com `total`, `page`, `pageSize`, `items` e `nextCursor`.
Campanhas, contatos e histórico de ligações também paginam por **cursor**, mais
indicado para percorrer listas grandes: passe o `nextCursor` como `cursor` da próxima
chamada. `nextCursor` é `null` na última página; com `cursor` informado, a API ignora `page`.

```php
$cursor = null;
do {
    $pagina = $cli->contacts->list(pageSize: 100, cursor: $cursor);
    foreach ($pagina->items as $contato) {
        echo $contato->name, PHP_EOL;
    }
    $cursor = $pagina->nextCursor;
} while ($cursor !== null);
```

---

## Tratamento de erros

Toda exceção do SDK implementa `WonitTecnologia\Interage\Exception\InterageException`.
Erros HTTP viram uma subclasse de `ApiException`, com `$statusCode`, `$apiStatus`,
`$apiMessage` e `$retryAfter`.

| Exceção | Quando |
|---|---|
| `BadRequestException` | 400 — parâmetros inválidos |
| `UnauthorizedException` | 401 — token ausente, inválido, expirado ou revogado |
| `ForbiddenException` | 403 — token sem a rota ou sem a ação (libere no painel) |
| `NotFoundException` | 404 — recurso não encontrado |
| `ConflictException` | 409 — conflito de estado (ex.: ramal já em chamada) |
| `UnprocessableEntityException` | 422 — ação não permitida no estado atual |
| `TooManyRequestsException` | 429 — limite de requisições excedido |
| `ServerException` | 5xx — erro interno da API |
| `TransportException` | falha de rede/conexão, sem resposta da API |
| `ValidationException` | parâmetro inválido detectado pelo SDK, antes de chamar a API |
| `UnexpectedResponseException` | resposta de sucesso em formato inesperado |

```php
use WonitTecnologia\Interage\Exception\ApiException;
use WonitTecnologia\Interage\Exception\ForbiddenException;
use WonitTecnologia\Interage\Exception\NotFoundException;
use WonitTecnologia\Interage\Exception\TooManyRequestsException;

try {
    $campanha = $cli->campaigns->get($id);
} catch (NotFoundException) {
    // campanha não existe
} catch (ForbiddenException $e) {
    // token válido, mas sem permissão para esta rota/ação
} catch (TooManyRequestsException $e) {
    sleep($e->retryAfter ?? 1);
    $campanha = $cli->campaigns->get($id);
} catch (ApiException $e) {
    error_log("{$e->statusCode} {$e->apiStatus}: {$e->apiMessage}");
}
```

O SDK não repete requisições sozinho — a decisão de esperar e tentar de novo fica com
quem chama.

---

## Testes

```bash
composer install
composer test
```

## Licença

MIT — ver [LICENSE](LICENSE).
