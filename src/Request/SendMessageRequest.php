<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Request;

use WonitTecnologia\Interage\Exception\ValidationException;

/**
 * Envio de mensagem não-template sobre uma conversa com sessão ativa de 24h.
 * Prefira os construtores nomeados: text(), media() e location().
 */
final class SendMessageRequest
{
    /**
     * @param string $protocol Protocolo da conversa ativa.
     * @param string $type     text | image | video | file | audio | sticker | location.
     */
    public function __construct(
        public readonly string $protocol,
        public readonly string $type,
        public readonly ?string $text = null,
        public readonly ?string $url = null,
        public readonly ?string $caption = null,
        public readonly ?string $filename = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $address = null,
        public readonly ?string $locationName = null,
    ) {
    }

    public static function text(string $protocol, string $text): self
    {
        return new self($protocol, 'text', text: $text);
    }

    /**
     * @param string $type image | video | file | audio | sticker.
     */
    public static function media(string $protocol, string $type, string $url, ?string $caption = null, ?string $filename = null): self
    {
        return new self($protocol, $type, url: $url, caption: $caption, filename: $filename);
    }

    public static function location(string $protocol, float $latitude, float $longitude, ?string $name = null, ?string $address = null): self
    {
        return new self($protocol, 'location', latitude: $latitude, longitude: $longitude, address: $address, locationName: $name);
    }

    /**
     * @internal
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function toArray(): array
    {
        if (trim($this->protocol) === '' || trim($this->type) === '') {
            throw new ValidationException('interage: SendMessageRequest::$protocol e $type são obrigatórios');
        }
        return Payload::compact([
            'protocol' => $this->protocol,
            'type' => $this->type,
            'text' => $this->text,
            'url' => $this->url,
            'caption' => $this->caption,
            'filename' => $this->filename,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
            'location_name' => $this->locationName,
        ]);
    }
}
