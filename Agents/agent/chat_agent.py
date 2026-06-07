"""
Conversational chat agent with role-based database access.

Uses LangChain's tool-calling mechanism with a ChatOpenAI model.
Maintains per-session conversation memory in the database.
"""
from __future__ import annotations

import json
import logging
import os
import textwrap
import time
from typing import Optional

from dotenv import load_dotenv, dotenv_values
from langchain_core.messages import AIMessage, HumanMessage, SystemMessage
from langchain_core.tools import tool
from langchain_openai import ChatOpenAI

from agent.chat_db import (
    admin_list_all_users,
    admin_list_users_by_role,
    admin_send_message,
    admin_send_message_by_name,
    get_stats,
    get_user_context,
    list_absences,
    list_interns,
    list_internships,
    list_tasks,
    load_chat_messages,
    save_chat_message,
    search_intern_by_name,
)

load_dotenv()

log = logging.getLogger("chat.agent")

# ─────────────────────────────────────────────────────────────────────────────
# Contextual tools — these close over the user context at call time
# ─────────────────────────────────────────────────────────────────────────────

def _build_tools(user_id: int, role: str, intern_id: Optional[int]):
    """Build LangChain tools scoped to the current user."""

    @tool
    def get_my_interns() -> str:
        """List all interns visible to you. Admins see all, Encadrants see their supervised interns, Stagiaires see only themselves."""
        rows = list_interns(user_id, role, intern_id)
        if not rows:
            return "Aucun stagiaire trouvé."
        return json.dumps(rows, ensure_ascii=False, indent=2)

    @tool
    def get_my_internships() -> str:
        """List all internships visible to you. Returns title, department, status, dates, supervisor, and assigned interns."""
        rows = list_internships(user_id, role, intern_id)
        if not rows:
            return "Aucun stage trouvé."
        return json.dumps(rows, ensure_ascii=False, indent=2)

    @tool
    def get_my_tasks() -> str:
        """List all tasks visible to you. Includes title, status, due date, and assignment info."""
        rows = list_tasks(user_id, role, intern_id)
        if not rows:
            return "Aucune tâche trouvée."
        return json.dumps(rows, ensure_ascii=False, indent=2)

    @tool
    def get_my_absences() -> str:
        """List absences visible to you. Returns dates, reasons, and justification status."""
        rows = list_absences(user_id, role, intern_id)
        if not rows:
            return "Aucune absence trouvée."
        return json.dumps(rows, ensure_ascii=False, indent=2)

    @tool
    def get_my_stats() -> str:
        """Get aggregate statistics (task counts, completion rates, absences) scoped to your permissions."""
        stats = get_stats(user_id, role, intern_id)
        return json.dumps(stats, ensure_ascii=False, indent=2)

    @tool
    def search_intern(name: str) -> str:
        """Search for an intern by name. Only returns interns you are authorized to see.
        Args:
            name: The full or partial name of the intern to search for.
        """
        rows = search_intern_by_name(name, user_id, role)
        if not rows:
            if role == "Stagiaire":
                return "Vous n'avez pas accès aux informations d'autres stagiaires."
            return f"Aucun stagiaire trouvé correspondant à '{name}'."
        return json.dumps(rows, ensure_ascii=False, indent=2)

    tools_list = [
        get_my_interns, get_my_internships, get_my_tasks, 
        get_my_absences, get_my_stats, search_intern
    ]

    # Admin-only tools
    if role == "Administrateur":
        @tool
        def send_internal_message(receiver_email: str, subject: str, body: str) -> str:
            """
            Send an internal message to a user by their email address.
            Returns success status or an error message.
            """
            result = admin_send_message(user_id, receiver_email, subject, body)
            return json.dumps(result, ensure_ascii=False, indent=2)

        @tool
        def send_internal_message_by_name(receiver_name: str, subject: str, body: str) -> str:
            """
            Send an internal message to a user by searching for their name.
            If multiple users match, it returns an error asking for the email.
            """
            result = admin_send_message_by_name(user_id, receiver_name, subject, body)
            return json.dumps(result, ensure_ascii=False, indent=2)

        @tool
        def list_all_users() -> str:
            """List all users in the system with their roles and active status."""
            rows = admin_list_all_users()
            return json.dumps(rows, ensure_ascii=False, indent=2)
            
        @tool
        def list_users_by_role(role_name: str) -> str:
            """
            List users filtered by a specific role.
            Common roles: 'Stagiaire', 'Encadrant', 'Responsable de competence', 'Administrateur', 'Responsable RH'
            """
            rows = admin_list_users_by_role(role_name)
            return json.dumps(rows, ensure_ascii=False, indent=2)
            
        tools_list.extend([send_internal_message, send_internal_message_by_name, list_all_users, list_users_by_role])

    return tools_list


# ─────────────────────────────────────────────────────────────────────────────
# System prompt — defines persona and access rules
# ─────────────────────────────────────────────────────────────────────────────

def _system_prompt(user_ctx: dict) -> str:
    role = user_ctx["role"]
    name = user_ctx["full_name"]

    access_rules = {
        "Stagiaire": (
            "Tu ne peux accéder QU'AUX données de ce stagiaire. "
            "Si on te demande des informations sur un autre stagiaire ou un stage qui n'est pas le sien, "
            "réponds poliment : « Désolé, vous n'avez pas accès à ces informations. »"
        ),
        "Encadrant": (
            "Tu peux accéder uniquement aux données des stagiaires et stages supervisés par cet encadrant. "
            "Si on te demande des infos sur des stagiaires ou stages d'un autre encadrant, "
            "réponds : « Désolé, vous n'avez accès qu'aux données de vos propres stagiaires. »"
        ),
        "Administrateur": (
            "Tu as accès à TOUTES les données de la plateforme. "
            "Tu as également la capacité d'envoyer des messages internes aux utilisateurs en utilisant les outils appropriés."
        ),
        "Responsable de competence": "Tu as accès à TOUTES les données de la plateforme.",
        "Responsable RH": "Tu as accès à TOUTES les données de la plateforme.",
    }

    return textwrap.dedent(f"""\
        Tu es l'assistant IA de la plateforme « Gestion des Stagiaires ».
        Tu aides les utilisateurs à consulter et comprendre les données de la plateforme.

        L'UTILISATEUR ACTUEL :
        - Nom : {name}
        - Rôle : {role}
        - Email : {user_ctx['email']}

        RÈGLES D'ACCÈS :
        {access_rules.get(role, "Accès standard.")}

        INSTRUCTIONS :
        - Réponds toujours en français.
        - Sois concis, professionnel et bienveillant.
        - Utilise les outils disponibles pour chercher des données ou accomplir des actions avant de répondre.
        - Ne fabrique JAMAIS de données. Si tu n'as pas l'information, dis-le.
        - Formate tes réponses avec du markdown simple (listes, gras) pour la lisibilité.
        - Si l'utilisateur pose une question hors du scope de la plateforme, redirige-le poliment.
    """)


# ─────────────────────────────────────────────────────────────────────────────
# Main chat function
# ─────────────────────────────────────────────────────────────────────────────

def chat(
    user_id: int,
    message: str,
    session_id: int,
    model: Optional[str] = None,
) -> str:
    """
    Process a chat message and return the assistant's response.
    Maintains conversation history per session_id using the database.
    """
    t0 = time.perf_counter()

    # 1. Resolve the user
    user_ctx = get_user_context(user_id)
    if "error" in user_ctx:
        return f"❌ {user_ctx['error']}"

    role      = user_ctx["role"]
    intern_id = user_ctx.get("intern_id")

    log.info("💬  chat() user=%s (%s)  session=%s  model=%s",
             user_ctx["full_name"], role, session_id, model or "default")

    # 2. Build tools scoped to this user
    tools = _build_tools(user_id, role, intern_id)

    # 3. Create the LLM
    config = dotenv_values(".env")
    chosen_model = model or config.get("CHAT_DEFAULT_MODEL", "deepseek/deepseek-v4-flash")
    llm = ChatOpenAI(
        model=chosen_model,
        api_key=config.get("OPENROUTER_API_KEY", ""),
        base_url=config.get("OPENROUTER_BASE_URL", "https://openrouter.ai/api/v1"),
        temperature=0.4,
        max_tokens=1500,
    )
    llm_with_tools = llm.bind_tools(tools)

    # 4. Build message list (system + history + new message)
    db_history = load_chat_messages(session_id, limit=30)
    
    messages = [SystemMessage(content=_system_prompt(user_ctx))]
    for msg in db_history:
        if msg["role"] == "user":
            messages.append(HumanMessage(content=msg["content"]))
        else:
            messages.append(AIMessage(content=msg["content"]))
            
    messages.append(HumanMessage(content=message))

    # 5. Invoke — handle tool calls iteratively
    max_tool_rounds = 4
    for round_num in range(max_tool_rounds + 1):
        response = llm_with_tools.invoke(messages)
        messages.append(response)

        # If the model wants to call tools
        if response.tool_calls:
            log.info("🔧  round %d → %d tool call(s): %s",
                     round_num + 1,
                     len(response.tool_calls),
                     [tc["name"] for tc in response.tool_calls])

            tool_map = {t.name: t for t in tools}
            for tc in response.tool_calls:
                tool_fn = tool_map.get(tc["name"])
                if tool_fn:
                    try:
                        result = tool_fn.invoke(tc["args"])
                    except Exception as exc:
                        result = f"Erreur outil : {exc}"
                    log.info("   🔧  %s → %d chars", tc["name"], len(str(result)))
                else:
                    result = f"Outil '{tc['name']}' inconnu."

                from langchain_core.messages import ToolMessage
                messages.append(ToolMessage(content=str(result), tool_call_id=tc["id"]))
        else:
            # Final response — no more tool calls
            break

    # 6. Extract text
    answer = response.content or "Je n'ai pas pu générer de réponse."

    # 7. Save to history (user message + final assistant message only)
    save_chat_message(session_id, "user", message)
    save_chat_message(session_id, "assistant", answer)

    elapsed = time.perf_counter() - t0
    log.info("✅  chat() → %d chars in %.1fs  (model=%s)", len(answer), elapsed, chosen_model)

    return answer
