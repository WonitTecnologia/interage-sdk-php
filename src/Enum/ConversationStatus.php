<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Enum;

/**
 * Status de uma conversa ativa (filtro de Omni::listConversations).
 */
enum ConversationStatus: string
{
    /** Em atendimento pelo chatbot. */
    case Bot = 'bot';
    /** Em atendimento pelo agente de IA. */
    case AiAgent = 'ai_agent';
    /** Aguardando na fila. */
    case Queue = 'queue';
    /** Em atendimento por um agente humano. */
    case Attending = 'attending';
}
