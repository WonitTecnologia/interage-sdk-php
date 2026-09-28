<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Request;

use WonitTecnologia\Interage\Enum\CollisionPolicy;
use WonitTecnologia\Interage\Exception\ValidationException;

/**
 * Criação de até 500 contatos de uma vez na central de contatos.
 */
final class BatchCreateContactsRequest
{
    public const MAX_CONTACTS = 500;

    /**
     * @param list<BatchContactItem> $contacts        De 1 a 500 contatos.
     * @param CollisionPolicy|null   $collisionPolicy Tratamento quando uma identidade já existe em
     *                                                outro contato (padrão da API: Ignore).
     */
    public function __construct(
        public readonly array $contacts,
        public readonly ?CollisionPolicy $collisionPolicy = null,
    ) {
    }

    /**
     * @internal
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function toArray(): array
    {
        if ($this->contacts === []) {
            throw new ValidationException('interage: informe ao menos um contato');
        }
        if (count($this->contacts) > self::MAX_CONTACTS) {
            throw new ValidationException('interage: lote excede o máximo de ' . self::MAX_CONTACTS . ' contatos');
        }
        $contacts = [];
        foreach ($this->contacts as $contact) {
            if (!$contact instanceof BatchContactItem) {
                throw new ValidationException('interage: BatchCreateContactsRequest::$contacts deve conter BatchContactItem');
            }
            $contacts[] = $contact->toArray();
        }
        return Payload::compact([
            'collision_policy' => $this->collisionPolicy?->value,
            'contacts' => $contacts,
        ]);
    }
}
