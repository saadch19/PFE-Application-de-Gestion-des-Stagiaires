"""
LangGraph state definition for the Encadrant Copilot weekly summary agent.
"""
from typing import Optional
from typing_extensions import TypedDict


class WeeklySummaryState(TypedDict):
    # ── Input ─────────────────────────────────────────────────
    intern_id: int
    week_start: str          # YYYY-MM-DD
    week_end: str            # YYYY-MM-DD

    # ── Data fetched from MySQL ────────────────────────────────
    intern_info: Optional[dict]       # name, school, specialty …
    internship_info: Optional[dict]   # title, department, supervisor …
    tasks: Optional[list]             # task rows for the period
    messages: Optional[list]          # message rows for the period
    daily_logs: Optional[list]        # daily journal entries (presence + notes)

    # ── Intermediate LLM outputs ──────────────────────────────
    task_analysis: Optional[str]      # JSON string from analyze_tasks node
    sentiment_analysis: Optional[str] # JSON string from analyze_sentiment node

    # ── Final structured report ───────────────────────────────
    report: Optional[dict]

    # ── Error propagation ─────────────────────────────────────
    error: Optional[str]
