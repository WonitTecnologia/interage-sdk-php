<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Enum;

/**
 * O que fazer quando o contato já existe na base (campanhas e criação em lote).
 * A colisão é detectada pelas identidades (canal + valor).
 */
enum CollisionPolicy: string
{
    /** Mantém os dados atuais do contato; não sobrescreve nada. */
    case Ignore = 'ignore';
    /** Sobrescreve os campos informados. */
    case Overwrite = 'overwrite';
    /** Preenche apenas os campos vazios do contato. */
    case UpdateEmpty = 'update_empty';
}
