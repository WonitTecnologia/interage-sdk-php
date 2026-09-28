<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Exception;

/**
 * 403 — token válido, mas a rota não foi liberada para ele ou falta a ação exigida (leitura, listagem, criação, alteração ou remoção). Trocar de token não resolve: libere a ação nas permissões do token no painel.
 */
class ForbiddenException extends ApiException
{
}
