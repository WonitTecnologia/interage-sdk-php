<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Model;

use WonitTecnologia\Interage\Internal\Data;

/**
 * Contato da central de contatos.
 */
final class Contact
{
    /**
     * @param array<string, mixed> $customInfo Campos personalizados (chave = key do campo cadastrado na central).
     * @param list<ContactIdentity> $identities
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $cpf,
        public readonly ?string $email,
        public readonly ?string $company,
        public readonly array $customInfo,
        public readonly bool $replaceName,
        public readonly string $visibility,
        public readonly array $identities,
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
            Data::string($data, 'id'),
            Data::string($data, 'name'),
            Data::nullableString($data, 'cpf'),
            Data::nullableString($data, 'email'),
            Data::nullableString($data, 'company'),
            Data::map($data, 'custom_info'),
            Data::bool($data, 'replace_name'),
            Data::string($data, 'visibility'),
            Data::list($data, 'identities', static fn (array $i): ContactIdentity => ContactIdentity::fromArray($i)),
            Data::date($data, 'created_at'),
            Data::date($data, 'updated_at'),
        );
    }
}
