"""
LangGraph node functions for the Encadrant Copilot weekly summary agent.

Graph flow:
  validate_input
      └─► fetch_data
              └─► analyze_tasks
                      └─► analyze_sentiment
                              └─► generate_report
                                      └─► END

Each node receives the full state dict and returns a partial update dict.
"""
from __future__ import annotations

import json
import logging
import os
import textwrap
import time
from datetime import date

from dotenv import load_dotenv
from langchain_core.messages import HumanMessage, SystemMessage
from langchain_openai import ChatOpenAI

from agent.database import (
    fetch_daily_logs_for_period,
    fetch_intern_info,
    fetch_internship_info,
    fetch_messages_for_period,
    fetch_tasks_for_period,
)
from agent.state import WeeklySummaryState

load_dotenv()

# ─────────────────────────────────────────────────────────────────────────────
# Logging setup — rich terminal output
# ─────────────────────────────────────────────────────────────────────────────

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s %(message)s",
    datefmt="%H:%M:%S",
)
log = logging.getLogger("copilot")

ICONS = {
    "validate":  "🔍",
    "fetch":     "📦",
    "tasks":     "🧠",
    "sentiment": "💭",
    "report":    "📝",
    "ok":        "✅",
    "warn":      "⚠️ ",
    "err":       "❌",
    "llm":       "🤖",
    "db":        "🗄️ ",
}

def _log(icon_key: str, msg: str) -> None:
    log.info("%s  %s", ICONS.get(icon_key, "▶"), msg)


# ─────────────────────────────────────────────────────────────────────────────
# Shared LLM instance (OpenRouter)
# ─────────────────────────────────────────────────────────────────────────────

def _get_llm() -> ChatOpenAI:
    return ChatOpenAI(
        model=os.getenv("OPENROUTER_MODEL", "openai/gpt-4o-mini"),
        api_key=os.getenv("OPENROUTER_API_KEY", ""),
        base_url=os.getenv("OPENROUTER_BASE_URL", "https://openrouter.ai/api/v1"),
        temperature=0.3,
        max_tokens=2048,
    )


def _call_llm(node_name: str, system_prompt: str, user_prompt: str) -> str:
    model = os.getenv("OPENROUTER_MODEL", "openai/gpt-4o-mini")
    _log("llm", f"{node_name} → calling LLM ({model})  [~{len(user_prompt)//4} tokens est.]")
    t0 = time.perf_counter()

    llm = _get_llm()
    messages = [
        SystemMessage(content=system_prompt),
        HumanMessage(content=user_prompt),
    ]
    response = llm.invoke(messages)
    elapsed = time.perf_counter() - t0
    _log("llm", f"{node_name} → LLM response received in {elapsed:.1f}s")
    return response.content.strip()


def _parse_json_response(raw: str) -> dict:
    """Best-effort JSON extraction from an LLM response."""
    for fence in ("```json", "```"):
        raw = raw.replace(fence, "")
    raw = raw.strip().strip("`").strip()
    try:
        return json.loads(raw)
    except json.JSONDecodeError:
        return {"raw": raw}


# ─────────────────────────────────────────────────────────────────────────────
# Node 1 – validate_input
# ─────────────────────────────────────────────────────────────────────────────

def validate_input(state: WeeklySummaryState) -> dict:
    _log("validate", "validate_input → checking request parameters")

    intern_id = state.get("intern_id")
    if not intern_id:
        _log("err", "validate_input → missing intern_id")
        return {"error": "intern_id is required."}

    week_start = state.get("week_start")
    week_end = state.get("week_end")

    if not week_start or not week_end:
        from agent.database import previous_week_bounds
        week_start, week_end = previous_week_bounds()
        _log("validate", f"validate_input → defaulting to previous week ({week_start} → {week_end})")
    else:
        _log("validate", f"validate_input → period: {week_start} → {week_end}")

    _log("ok", f"validate_input → intern_id={intern_id}  week={week_start}/{week_end}")
    return {
        "intern_id": int(intern_id),
        "week_start": week_start,
        "week_end": week_end,
        "error": None,
    }


# ─────────────────────────────────────────────────────────────────────────────
# Node 2 – fetch_data
# ─────────────────────────────────────────────────────────────────────────────

def fetch_data(state: WeeklySummaryState) -> dict:
    intern_id  = state["intern_id"]
    week_start = state["week_start"]
    week_end   = state["week_end"]

    _log("fetch", f"fetch_data → connecting to MySQL  (intern_id={intern_id})")
    try:
        intern_info = fetch_intern_info(intern_id)
        if intern_info is None:
            _log("err", f"fetch_data → intern {intern_id} not found in DB")
            return {"error": f"Intern with id={intern_id} not found."}

        internship_info = fetch_internship_info(intern_id)
        tasks       = fetch_tasks_for_period(intern_id, week_start, week_end)
        messages    = fetch_messages_for_period(intern_id, week_start, week_end)
        daily_logs  = fetch_daily_logs_for_period(intern_id, week_start, week_end)

        _log("db", (
            f"fetch_data → intern='{intern_info['full_name']}'  "
            f"tasks={len(tasks)}  messages={len(messages)}  daily_logs={len(daily_logs)}"
        ))

        # Summarise daily log presence
        present_days = sum(1 for d in daily_logs if d.get("is_present"))
        absent_days  = len(daily_logs) - present_days
        if daily_logs:
            _log("fetch", f"fetch_data → journal: {present_days} présent(s), {absent_days} absent(s)")
        else:
            _log("warn", "fetch_data → no daily journal entries found for this period")

        return {
            "intern_info":      intern_info,
            "internship_info":  internship_info,
            "tasks":            tasks,
            "messages":         messages,
            "daily_logs":       daily_logs,
        }
    except Exception as exc:
        _log("err", f"fetch_data → database error: {exc}")
        return {"error": f"Database error: {exc}"}


# ─────────────────────────────────────────────────────────────────────────────
# Node 3 – analyze_tasks
# ─────────────────────────────────────────────────────────────────────────────

def analyze_tasks(state: WeeklySummaryState) -> dict:
    intern     = state["intern_info"]
    tasks      = state.get("tasks", [])
    daily_logs = state.get("daily_logs", [])
    week_start = state["week_start"]
    week_end   = state["week_end"]

    _log("tasks", f"analyze_tasks → {len(tasks)} task(s) to analyse")

    if not tasks:
        _log("warn", "analyze_tasks → no tasks found, returning empty analysis")
        return {
            "task_analysis": json.dumps({
                "achievements": [],
                "blockers": [],
                "overdue_tasks": [],
                "completion_rate": 0,
                "assessment": "Aucune tâche trouvée pour cette période.",
            })
        }

    # Format task list
    tasks_text = "\n".join(
        f"- [{t['status'].upper()}] {t['title']} "
        f"(échéance: {t.get('due_date') or 'N/A'}) "
        f"— Commentaire hebdo: {t.get('weekly_comment') or 'Aucun'}"
        for t in tasks
    )

    # Format daily journal
    journal_text = ""
    if daily_logs:
        journal_lines = []
        for d in daily_logs:
            presence = "PRÉSENT" if d.get("is_present") else "ABSENT"
            note = d.get("daily_note") or "(aucune note)"
            journal_lines.append(f"- {d['log_date']} [{presence}] : {note[:400]}")
        journal_text = "\nJOURNAL QUOTIDIEN DU STAGIAIRE :\n" + "\n".join(journal_lines)
    else:
        journal_text = "\nJOURNAL QUOTIDIEN : Aucune saisie pour cette semaine."

    system_prompt = textwrap.dedent("""\
        Tu es un assistant RH expert en analyse de performance de stagiaires.
        Tu dois analyser les tâches et le journal quotidien d'un stagiaire pour une semaine
        donnée et retourner un JSON structuré. Réponds UNIQUEMENT avec le JSON.
    """)

    user_prompt = textwrap.dedent(f"""\
        Stagiaire : {intern['full_name']} ({intern.get('specialty', 'N/A')})
        École : {intern.get('school', 'N/A')}
        Semaine : du {week_start} au {week_end}

        TÂCHES :
        {tasks_text}
        {journal_text}

        Analyse et retourne un JSON avec exactement ces clés :
        {{
          "achievements": ["liste des réalisations concrètes cette semaine (basées sur tâches terminées ET journal)"],
          "blockers": ["blocages ou difficultés identifiés dans les tâches ou le journal"],
          "overdue_tasks": ["tâches dont la date d'échéance est dépassée et le statut pas 'termine'"],
          "completion_rate": <pourcentage entier 0-100 basé sur les tâches terminées vs total>,
          "assessment": "<évaluation courte en une phrase>"
        }}
    """)

    raw = _call_llm("analyze_tasks", system_prompt, user_prompt)
    parsed = _parse_json_response(raw)
    _log("tasks", f"analyze_tasks → completion_rate={parsed.get('completion_rate', '?')}%  blockers={len(parsed.get('blockers', []))}")
    return {"task_analysis": raw}


# ─────────────────────────────────────────────────────────────────────────────
# Node 4 – analyze_sentiment
# ─────────────────────────────────────────────────────────────────────────────

def analyze_sentiment(state: WeeklySummaryState) -> dict:
    intern     = state["intern_info"]
    messages   = state.get("messages", [])
    tasks      = state.get("tasks", [])
    daily_logs = state.get("daily_logs", [])
    week_start = state["week_start"]
    week_end   = state["week_end"]

    _log("sentiment", f"analyze_sentiment → {len(daily_logs)} journal entries + {len(messages)} messages")

    corpus_parts = []

    # Daily journal notes (primary source)
    if daily_logs:
        journal_lines = []
        for d in daily_logs:
            presence = "PRÉSENT" if d.get("is_present") else "ABSENT"
            note = d.get("daily_note") or ""
            if note or not d.get("is_present"):
                journal_lines.append(f"[{d['log_date']} – {presence}] {note[:500]}")
        if journal_lines:
            corpus_parts.append("Journal quotidien :\n" + "\n".join(journal_lines))

    # Messages
    if messages:
        msg_lines = []
        for m in messages:
            direction = "Envoyé" if m.get("direction") == "sent" else "Reçu"
            msg_lines.append(f"[{direction}] {m.get('subject', '')} — {m.get('body', '')[:300]}")
        corpus_parts.append("Messages :\n" + "\n".join(msg_lines))

    # Weekly task comments
    comments = [t["weekly_comment"] for t in tasks if t.get("weekly_comment")]
    if comments:
        corpus_parts.append("Commentaires sur les tâches :\n" + "\n".join(f"- {c}" for c in comments))

    if not corpus_parts:
        _log("warn", "analyze_sentiment → no corpus available, returning neutral sentiment")
        return {
            "sentiment_analysis": json.dumps({
                "overall_sentiment": "neutral",
                "sentiment_label": "Neutre",
                "engagement_score": 5,
                "observations": ["Aucune communication ni journal disponible pour cette période."],
                "red_flags": [],
            })
        }

    corpus = "\n\n".join(corpus_parts)

    system_prompt = textwrap.dedent("""\
        Tu es un expert en psychologie du travail et en analyse de sentiment.
        Tu analyses le journal quotidien et les communications d'un stagiaire
        pour détecter son état émotionnel, son engagement et d'éventuels signaux d'alarme.
        Réponds UNIQUEMENT avec le JSON demandé.
    """)

    user_prompt = textwrap.dedent(f"""\
        Stagiaire : {intern['full_name']}
        Semaine : du {week_start} au {week_end}

        DONNÉES D'ANALYSE :
        {corpus}

        Retourne un JSON avec exactement ces clés :
        {{
          "overall_sentiment": "<positive | neutral | negative | concerning>",
          "sentiment_label": "<Positif | Neutre | Négatif | Préoccupant>",
          "engagement_score": <entier de 1 (très désengagé) à 10 (très engagé)>,
          "observations": ["observations clés sur l'état émotionnel et le niveau d'implication du stagiaire"],
          "red_flags": ["signaux d'alarme à porter à l'attention urgente de l'encadrant, ou liste vide"]
        }}
    """)

    raw = _call_llm("analyze_sentiment", system_prompt, user_prompt)
    parsed = _parse_json_response(raw)
    _log("sentiment", (
        f"analyze_sentiment → sentiment={parsed.get('overall_sentiment', '?')}  "
        f"engagement={parsed.get('engagement_score', '?')}/10  "
        f"red_flags={len(parsed.get('red_flags', []))}"
    ))
    return {"sentiment_analysis": raw}


# ─────────────────────────────────────────────────────────────────────────────
# Node 5 – generate_report
# ─────────────────────────────────────────────────────────────────────────────

def generate_report(state: WeeklySummaryState) -> dict:
    intern             = state["intern_info"]
    internship         = state.get("internship_info") or {}
    task_analysis_raw  = state.get("task_analysis", "{}")
    sentiment_raw      = state.get("sentiment_analysis", "{}")
    daily_logs         = state.get("daily_logs", [])
    week_start         = state["week_start"]
    week_end           = state["week_end"]

    _log("report", "generate_report → building final report")

    task_data      = _parse_json_response(task_analysis_raw)
    sentiment_data = _parse_json_response(sentiment_raw)

    # Presence summary from daily logs
    present_days = sum(1 for d in daily_logs if d.get("is_present"))
    total_days   = len(daily_logs)

    system_prompt = textwrap.dedent("""\
        Tu es un assistant qui rédige des rapports hebdomadaires de suivi de stagiaires
        destinés aux encadrants. Ton style est professionnel, concis et bienveillant.
        Réponds UNIQUEMENT avec le JSON demandé.
    """)

    user_prompt = textwrap.dedent(f"""\
        Génère le rapport hebdomadaire pour l'encadrant.

        STAGIAIRE : {intern['full_name']}
        ÉCOLE : {intern.get('school', 'N/A')} — Spécialité : {intern.get('specialty', 'N/A')}
        STAGE : {internship.get('title', 'N/A')} ({internship.get('department', 'N/A')})
        ENCADRANT : {internship.get('supervisor_name', 'N/A')}
        SEMAINE : du {week_start} au {week_end}
        PRÉSENCE JOURNALISÉE : {present_days}/{total_days} jours documentés comme présent

        ANALYSE DES TÂCHES :
        {json.dumps(task_data, ensure_ascii=False, indent=2)}

        ANALYSE DU SENTIMENT :
        {json.dumps(sentiment_data, ensure_ascii=False, indent=2)}

        Retourne un JSON avec exactement ces clés :
        {{
          "executive_summary": "<résumé exécutif en 2-3 phrases mentionnant présence, avancement et état général>",
          "achievements": ["réalisations clés de la semaine"],
          "blockers": ["blocages et défis rencontrés"],
          "sentiment_summary": "<résumé de l'état du stagiaire en 1-2 phrases>",
          "recommended_actions": ["actions recommandées à l'encadrant"],
          "overall_rating": "<Excellent | Bien | Moyen | Nécessite attention>",
          "rating_color": "<success | primary | warning | danger>",
          "week_score": <score entier de 0 à 100>
        }}
    """)

    raw = _call_llm("generate_report", system_prompt, user_prompt)
    report_data = _parse_json_response(raw)

    # Enrich with metadata
    report_data["intern_name"]          = intern["full_name"]
    report_data["intern_school"]        = intern.get("school", "")
    report_data["intern_specialty"]     = intern.get("specialty", "")
    report_data["internship_title"]     = internship.get("title", "")
    report_data["supervisor_name"]      = internship.get("supervisor_name", "")
    report_data["week_start"]           = week_start
    report_data["week_end"]             = week_end
    report_data["task_completion_rate"] = task_data.get("completion_rate", 0)
    report_data["engagement_score"]     = sentiment_data.get("engagement_score", 5)
    report_data["overall_sentiment"]    = sentiment_data.get("overall_sentiment", "neutral")
    report_data["red_flags"]            = sentiment_data.get("red_flags", [])
    report_data["overdue_tasks"]        = task_data.get("overdue_tasks", [])
    report_data["presence_days"]        = present_days
    report_data["total_logged_days"]    = total_days

    _log("ok", (
        f"generate_report → DONE  rating='{report_data.get('overall_rating', '?')}'  "
        f"score={report_data.get('week_score', '?')}/100  "
        f"presence={present_days}/{total_days}"
    ))
    return {"report": report_data}
