"""
MySQL query helpers — connects to the same database as the Laravel app.
All functions return plain Python dicts / lists (JSON-serialisable).
"""
from __future__ import annotations

import os
from datetime import date, timedelta
from typing import Optional

import mysql.connector
from dotenv import load_dotenv

load_dotenv()


# ─────────────────────────────────────────────────────────────────────────────
# Connection
# ─────────────────────────────────────────────────────────────────────────────

def _get_conn() -> mysql.connector.MySQLConnection:
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        port=int(os.getenv("DB_PORT", 3306)),
        database=os.getenv("DB_NAME", "pfe_stagiaires"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASSWORD", ""),
        charset="utf8mb4",
    )


def _dict_rows(cursor) -> list[dict]:
    """Return all rows as a list of plain dicts with JSON-safe values."""
    rows = cursor.fetchall()
    safe = []
    for row in rows:
        safe_row = {}
        for k, v in row.items():
            if isinstance(v, (date,)):
                safe_row[k] = v.isoformat()
            else:
                safe_row[k] = v
        safe.append(safe_row)
    return safe


# ─────────────────────────────────────────────────────────────────────────────
# Queries
# ─────────────────────────────────────────────────────────────────────────────

def fetch_intern_info(intern_id: int) -> Optional[dict]:
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT i.id, i.school, i.specialty,
               i.start_date, i.end_date, i.is_archived,
               u.full_name, u.email
        FROM   interns i
        JOIN   users   u ON u.id = i.user_id
        WHERE  i.id = %s
        LIMIT  1
        """,
        (intern_id,),
    )
    row = cur.fetchone()
    conn.close()
    if row is None:
        return None
    # Serialise date fields
    for field in ("start_date", "end_date"):
        if isinstance(row.get(field), date):
            row[field] = row[field].isoformat()
    return row


def fetch_internship_info(intern_id: int) -> Optional[dict]:
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT inv.id, inv.title, inv.description, inv.department, inv.status,
               inv.start_date, inv.end_date,
               sup.full_name  AS supervisor_name,
               resp.full_name AS responsible_name
        FROM   internships     inv
        JOIN   internship_intern ii  ON ii.internship_id = inv.id
        LEFT JOIN users sup  ON sup.id  = inv.supervisor_id
        LEFT JOIN users resp ON resp.id = inv.responsible_id
        WHERE  ii.intern_id = %s
        ORDER  BY inv.created_at DESC
        LIMIT  1
        """,
        (intern_id,),
    )
    row = cur.fetchone()
    conn.close()
    if row is None:
        return None
    for field in ("start_date", "end_date"):
        if isinstance(row.get(field), date):
            row[field] = row[field].isoformat()
    return row


def fetch_tasks_for_period(intern_id: int, week_start: str, week_end: str) -> list[dict]:
    """
    Return tasks that are:
      - assigned to this intern AND
      - either have a due_date in the window,
        were updated during the window,
        or are currently non-finished (always relevant).
    """
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT t.id, t.title, t.details, t.status,
               t.due_date, t.weekly_comment,
               t.created_at, t.updated_at,
               ab.full_name AS assigned_by_name,
               inv.title    AS internship_title
        FROM   tasks       t
        JOIN   internships inv ON inv.id = t.internship_id
        JOIN   internship_intern ii ON ii.internship_id = inv.id
        LEFT JOIN users ab ON ab.id = t.assigned_by
        WHERE  ii.intern_id = %s
          AND  t.assigned_to = (SELECT user_id FROM interns WHERE id = %s LIMIT 1)
          AND  (
                  (t.due_date    BETWEEN %s AND %s)
               OR (t.updated_at  BETWEEN %s AND %s)
               OR  t.status      != 'termine'
               )
        ORDER  BY t.due_date ASC
        """,
        (intern_id, intern_id, week_start, week_end, week_start, week_end),
    )
    rows = _dict_rows(cur)
    conn.close()
    return rows


def fetch_messages_for_period(intern_id: int, week_start: str, week_end: str) -> list[dict]:
    """Return messages sent or received by the intern during the given period."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT m.id, m.subject, m.body, m.created_at,
               s.full_name AS sender_name,
               r.full_name AS receiver_name,
               CASE WHEN m.sender_id = (SELECT user_id FROM interns WHERE id = %s LIMIT 1)
                    THEN 'sent' ELSE 'received' END AS direction
        FROM   messages m
        JOIN   users s ON s.id = m.sender_id
        JOIN   users r ON r.id = m.receiver_id
        WHERE  (
                  m.sender_id   = (SELECT user_id FROM interns WHERE id = %s LIMIT 1)
               OR m.receiver_id = (SELECT user_id FROM interns WHERE id = %s LIMIT 1)
               )
          AND  m.created_at BETWEEN %s AND %s
        ORDER  BY m.created_at ASC
        """,
        (intern_id, intern_id, intern_id, week_start, week_end),
    )
    rows = _dict_rows(cur)
    conn.close()
    return rows


def fetch_daily_logs_for_period(intern_id: int, week_start: str, week_end: str) -> list[dict]:
    """Return the intern's self-reported daily journal entries for the given period."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT dl.id, dl.log_date, dl.is_present, dl.daily_note, dl.created_at
        FROM   daily_logs dl
        WHERE  dl.intern_id = %s
          AND  dl.log_date BETWEEN %s AND %s
        ORDER  BY dl.log_date ASC
        """,
        (intern_id, week_start, week_end),
    )
    rows = _dict_rows(cur)
    conn.close()
    return rows


# ─────────────────────────────────────────────────────────────────────────────
# Utility
# ─────────────────────────────────────────────────────────────────────────────

def current_week_bounds() -> tuple[str, str]:
    """Return (Monday, Sunday) of the current week as ISO strings."""
    today = date.today()
    monday = today - timedelta(days=today.weekday())
    sunday = monday + timedelta(days=6)
    return monday.isoformat(), sunday.isoformat()


def previous_week_bounds() -> tuple[str, str]:
    """Return (Monday, Sunday) of the previous week as ISO strings."""
    today = date.today()
    last_monday = today - timedelta(days=today.weekday() + 7)
    last_sunday = last_monday + timedelta(days=6)
    return last_monday.isoformat(), last_sunday.isoformat()
