<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Request;

use WonitTecnologia\Interage\Enum\CollisionPolicy;
use WonitTecnologia\Interage\Exception\ValidationException;

/**
 * Dados para criar uma campanha com CSV de contatos anexo.
 *
 * O CSV deve ter delimitador `,` ou `;` (detectado automaticamente) e uma coluna de
 * telefone (phone, telefone, numero, celular ou whatsapp). Colunas opcionais
 * reconhecidas: name, email, company.
 */
final class CreateCampaignRequest
{
    /**
     * @param string                   $name            Nome da campanha.
     * @param string                   $instanceId      instance_id da instância de envio (Messages::listInstances).
     * @param string                   $templateId      gupshup_id do template HSM aprovado (Messages::listTemplates).
     * @param CollisionPolicy          $collisionPolicy Tratamento de contatos já existentes.
     * @param string                   $fileName        Nome do arquivo CSV (ex.: contatos.csv).
     * @param string                   $fileContent     Conteúdo do CSV (ex.: file_get_contents('contatos.csv')).
     * @param list<string>             $templateParams  Valores das variáveis do template, na ordem.
     * @param \DateTimeInterface|null  $startAt         Início do disparo.
     * @param \DateTimeInterface|null  $endAt           Limite do disparo (null = até concluir).
     * @param bool|null                $autoStart       true inicia sozinha em $startAt.
     * @param array<string, mixed>     $settings        Configurações extras (ex.: ['delay_ms' => 1000]).
     */
    public function __construct(
        public readonly string $name,
        public readonly string $instanceId,
        public readonly string $templateId,
        public readonly CollisionPolicy $collisionPolicy,
        public readonly string $fileName,
        public readonly string $fileContent,
        public readonly array $templateParams = [],
        public readonly ?string $description = null,
        public readonly ?\DateTimeInterface $startAt = null,
        public readonly ?\DateTimeInterface $endAt = null,
        public readonly ?bool $autoStart = null,
        public readonly array $settings = [],
    ) {
    }

    /**
     * @internal Campos de texto do multipart (o arquivo vai à parte).
     * @return array<string, string>
     * @throws ValidationException
     */
    public function toFields(): array
    {
        foreach (['name' => $this->name, 'instanceId' => $this->instanceId, 'templateId' => $this->templateId] as $field => $value) {
            if (trim($value) === '') {
                throw new ValidationException("interage: CreateCampaignRequest::\$$field é obrigatório");
            }
        }
        if (trim($this->fileName) === '' || $this->fileContent === '') {
            throw new ValidationException('interage: CreateCampaignRequest::$fileName e $fileContent são obrigatórios');
        }

        $fields = [
            'name' => $this->name,
            'channel' => 'whatsapp',
            'instance_id' => $this->instanceId,
            'template_id' => $this->templateId,
            'collision_policy' => $this->collisionPolicy->value,
        ];
        if ($this->templateParams !== []) {
            $fields['template_params'] = json_encode(array_values($this->templateParams), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }
        if ($this->description !== null && $this->description !== '') {
            $fields['description'] = $this->description;
        }
        if ($this->startAt !== null) {
            $fields['start_at'] = $this->startAt->format(\DateTimeInterface::RFC3339);
        }
        if ($this->endAt !== null) {
            $fields['end_at'] = $this->endAt->format(\DateTimeInterface::RFC3339);
        }
        if ($this->autoStart !== null) {
            $fields['auto_start'] = $this->autoStart ? 'true' : 'false';
        }
        if ($this->settings !== []) {
            $fields['settings'] = json_encode($this->settings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }
        return $fields;
    }
}
