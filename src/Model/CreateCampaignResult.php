<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Retorno da criação de campanha. A importação do CSV é assíncrona: a campanha nasce "pending" e passa a "ready" quando termina.
 */
final class CreateCampaignResult
{
    public function __construct(
        public readonly string $campaignId,
        public readonly string $status,
        public readonly string $mailingId,
        public readonly string $fileName,
    ) {
    }

    /**
     * @internal Usado pelo SDK para montar o modelo a partir da resposta da API.
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Data::string($data, 'campaign_id'),
            Data::string($data, 'status'),
            Data::string($data, 'mailing_id'),
            Data::string($data, 'file_name'),
        );
    }
}
