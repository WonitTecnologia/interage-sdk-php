<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Request;

use WonitTecnologia\Interage\Exception\ValidationException;

/**
 * Forma de contato de um contato do lote.
 */
final class ContactIdentityInput
{
    /**
     * @param string $channel   phone | whatsapp | telegram | instagram | facebook | sip.
     * @param string $idType    phone | username | handle | extension | profile_id.
     * @param string $idValue   Valor da identidade (ex.: 5511999998888). Máx. 255 caracteres.
     * @param bool   $isPrimary Identidade principal do contato.
     */
    public function __construct(
        public readonly string $channel,
        public readonly string $idType,
        public readonly string $idValue,
        public readonly bool $isPrimary = false,
    ) {
    }

    /** Atalho para um número de WhatsApp. */
    public static function whatsapp(string $phone, bool $isPrimary = false): self
    {
        return new self('whatsapp', 'phone', $phone, $isPrimary);
    }

    /** Atalho para um telefone. */
    public static function phone(string $phone, bool $isPrimary = false): self
    {
        return new self('phone', 'phone', $phone, $isPrimary);
    }

    /**
     * @internal
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function toArray(): array
    {
        if (trim($this->channel) === '' || trim($this->idType) === '' || trim($this->idValue) === '') {
            throw new ValidationException('interage: ContactIdentityInput exige $channel, $idType e $idValue');
        }
        return Payload::compact([
            'channel' => $this->channel,
            'id_type' => $this->idType,
            'id_value' => $this->idValue,
            'is_primary' => $this->isPrimary ?: null,
        ]);
    }
}
