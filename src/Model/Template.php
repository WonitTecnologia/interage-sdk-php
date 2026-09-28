<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Template HSM. Use $gupshupId como templateId nos envios e campanhas.
 */
final class Template
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $gupshupId,
        public readonly string $elementName,
        public readonly string $languageCode,
        public readonly string $category,
        public readonly string $templateType,
        public readonly string $status,
        public readonly string $content,
        public readonly ?string $header,
        public readonly ?string $footer,
        public readonly ?string $mediaUrl,
        public readonly string $visibility,
        public readonly string $instanceName,
        public readonly string $instanceSource,
        public readonly string $instanceId,
        public readonly int $paramsCount,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::int($data, 'id'),
            Data::nullableString($data, 'gupshup_id'),
            Data::string($data, 'element_name'),
            Data::string($data, 'language_code'),
            Data::string($data, 'category'),
            Data::string($data, 'template_type'),
            Data::string($data, 'status'),
            Data::string($data, 'content'),
            Data::nullableString($data, 'header'),
            Data::nullableString($data, 'footer'),
            Data::nullableString($data, 'media_url'),
            Data::string($data, 'visibility'),
            Data::string($data, 'instance_name'),
            Data::string($data, 'instance_source'),
            Data::string($data, 'instance_id'),
            Data::int($data, 'params_count'),
            Data::date($data, 'created_at'),
            Data::date($data, 'updated_at'),
        );
    }
}
