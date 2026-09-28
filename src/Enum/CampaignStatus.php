<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Enum;

/**
 * Status de uma campanha. {@see \WonitTecnologia\Interage\Model\Campaign::$status}
 * é string (a API pode ganhar status novos sem quebrar o SDK); compare com
 * CampaignStatus::Running->value ou use CampaignStatus::tryFrom($campanha->status).
 */
enum CampaignStatus: string
{
    /** Criada; CSV em importação. */
    case Pending = 'pending';
    /** Importando contatos. */
    case Processing = 'processing';
    /** Pronta para iniciar. */
    case Ready = 'ready';
    /** Agendada (auto_start futuro). */
    case Scheduled = 'scheduled';
    /** Em execução. */
    case Running = 'running';
    /** Pausada (retomável). */
    case Paused = 'paused';
    /** Concluída. */
    case Completed = 'completed';
    /** Cancelada (não reinicia). */
    case Canceled = 'canceled';
    /** Falhou. */
    case Failed = 'failed';
}
