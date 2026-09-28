<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Request;

use WonitTecnologia\Interage\Exception\ValidationException;

/**
 * Um contato do lote de Contacts::batchCreate().
 *
 * Limites da API: $name 255 caracteres, $cpf 14, $email/$company 255, até 10
 * identidades, até 20 etiquetas, $customInfo até 10 KB.
 */
final class BatchContactItem
{
    private const MAX_IDENTITIES = 10;
    private const MAX_LABELS = 20;

    /**
     * @param string                     $name        Nome do contato.
     * @param list<ContactIdentityInput> $identities  Formas de contato — ao menos uma.
     * @param array<string, string>      $customInfo  Campos personalizados (chave = key do campo
     *                                                cadastrado na central). Só texto: números e
     *                                                datas vão como string (ex.: '1990-05-20').
     * @param list<int>                  $labels      NÚMEROS das etiquetas (o número sequencial
     *                                                exibido em Admin → Contatos → Etiquetas), não
     *                                                os nomes. A etiqueta precisa existir e estar
     *                                                ativa, senão o item falha e nada é gravado
     *                                                para aquele contato.
     * @param bool|null                  $replaceName Permite substituir o nome pelo perfil do canal
     *                                                (ex.: nome do WhatsApp).
     */
    public function __construct(
        public readonly string $name,
        public readonly array $identities,
        public readonly ?string $cpf = null,
        public readonly ?string $email = null,
        public readonly ?string $company = null,
        public readonly array $customInfo = [],
        public readonly array $labels = [],
        public readonly ?bool $replaceName = null,
    ) {
    }

    /**
     * @internal
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function toArray(): array
    {
        if ($this->identities === []) {
            throw new ValidationException('interage: BatchContactItem exige ao menos uma identidade');
        }
        if (count($this->identities) > self::MAX_IDENTITIES) {
            throw new ValidationException('interage: BatchContactItem aceita no máximo ' . self::MAX_IDENTITIES . ' identidades');
        }
        if (count($this->labels) > self::MAX_LABELS) {
            throw new ValidationException('interage: BatchContactItem aceita no máximo ' . self::MAX_LABELS . ' etiquetas');
        }
        foreach ($this->labels as $label) {
            if (!is_int($label)) {
                throw new ValidationException('interage: BatchContactItem::$labels recebe os números das etiquetas (int), não os nomes');
            }
        }
        foreach ($this->customInfo as $key => $value) {
            if (!is_string($value)) {
                throw new ValidationException("interage: BatchContactItem::\$customInfo['$key'] deve ser string");
            }
        }

        $identities = [];
        foreach ($this->identities as $identity) {
            if (!$identity instanceof ContactIdentityInput) {
                throw new ValidationException('interage: BatchContactItem::$identities deve conter ContactIdentityInput');
            }
            $identities[] = $identity->toArray();
        }

        return Payload::compact([
            'name' => $this->name,
            'identities' => $identities,
            'cpf' => $this->cpf,
            'email' => $this->email,
            'company' => $this->company,
            // objeto JSON mesmo quando as chaves parecem índices
            'custom_info' => $this->customInfo === [] ? null : (object) $this->customInfo,
            'labels' => array_values($this->labels),
            'replace_name' => $this->replaceName,
        ]);
    }
}
