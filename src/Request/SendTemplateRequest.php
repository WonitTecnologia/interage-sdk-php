<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Request;

use WonitTecnologia\Interage\Exception\ValidationException;

/**
 * Envio de um template HSM aprovado.
 */
final class SendTemplateRequest
{
    /**
     * @param string       $to             Número do destinatário (ex.: 5547999999999).
     * @param string       $templateId     gupshup_id do template (Messages::listTemplates).
     * @param string       $instanceId     instance_id da instância do template.
     * @param list<string> $params         Valores das variáveis do template, na ordem.
     * @param string|null  $contactName    Nome do contato (criação/atualização do cadastro).
     * @param string|null  $mediaUrl       URL da mídia, para templates com header de mídia.
     * @param string|null  $mediaType      image | video | document.
     * @param string|null  $mediaFilename  Nome do arquivo (para document).
     * @param string|null  $assignedUserId Quando informado, cria um atendimento outbound atribuído ao agente.
     */
    public function __construct(
        public readonly string $to,
        public readonly string $templateId,
        public readonly string $instanceId,
        public readonly array $params = [],
        public readonly ?string $contactName = null,
        public readonly ?string $mediaUrl = null,
        public readonly ?string $mediaType = null,
        public readonly ?string $mediaFilename = null,
        public readonly ?string $assignedUserId = null,
    ) {
    }

    /**
     * @internal
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function toArray(): array
    {
        foreach (['to' => $this->to, 'templateId' => $this->templateId, 'instanceId' => $this->instanceId] as $field => $value) {
            if (trim($value) === '') {
                throw new ValidationException("interage: SendTemplateRequest::\$$field é obrigatório");
            }
        }
        return Payload::compact([
            'to' => $this->to,
            'template_id' => $this->templateId,
            'instance_id' => $this->instanceId,
            'params' => array_values($this->params),
            'contact_name' => $this->contactName,
            'media_url' => $this->mediaUrl,
            'media_type' => $this->mediaType,
            'media_filename' => $this->mediaFilename,
            'assigned_user_id' => $this->assignedUserId,
        ]);
    }
}
