<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Exception;

/**
 * 422 — ação não permitida no estado atual (ex.: iniciar campanha cancelada).
 */
class UnprocessableEntityException extends ApiException
{
}
