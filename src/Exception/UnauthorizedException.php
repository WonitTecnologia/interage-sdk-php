<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Exception;

/**
 * 401 — token ausente, inválido, expirado ou revogado. Corrija a credencial (gere ou renove o token no painel).
 */
class UnauthorizedException extends ApiException
{
}
