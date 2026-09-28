<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Enum;

/**
 * Destino de uma transferência de conversa.
 */
enum TransferTargetType: string
{
    /** Transfere para uma fila. */
    case Queue = 'queue';
    /** Transfere para um agente. */
    case User = 'user';
}
