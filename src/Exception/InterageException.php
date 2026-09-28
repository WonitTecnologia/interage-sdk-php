<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Exception;

/**
 * Marcador de todas as exceções lançadas pelo SDK. Capture esta interface para
 * tratar qualquer falha do SDK num só lugar:
 *
 *     try { ... } catch (InterageException $e) { ... }
 */
interface InterageException extends \Throwable
{
}
