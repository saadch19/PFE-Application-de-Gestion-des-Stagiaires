"""
Chat-specific database queries.

These functions provide read-only access to the application database,
returning results scoped by the calling user's role and permissions.
"""
from __future__ import annotations

import logging
from datetime import date, datetime
from typing import Optional

from agent.database import _get_conn, _dict_rows

log = logging.getLogger("chat.db")


# ─────────────────────────────────────────────────────────────────────────────
# Helpers
# ─────────────────────────────────────────────────────────────────────────────

def _safe_val(v):
    """Convert non-JSON-safe types."""
    if isinstance(v, date):
        return v.isoformat()
    if isinstance(v, datetime):
        return v.isoformat()
    return v


def _safe_rows(cursor) -> list[dict]:
    rows = cursor.fetchall()
    return [{k: _safe_val(v) for k, v in row.items()} for row in rows]


# ─────────────────────────────────────────────────────────────────────────────
# User context — who is calling and what can they see?
# ─────────────────────────────────────────────────────────────────────────────

def get_user_context(user_id: int) -> dict:
    """
    Return the calling user's role and permissions scope.
    This is the single source of truth for access control.
    """
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT u.id, u.full_name, u.email,
               r.name AS role_name,
               i.id   AS intern_id
        FROM   users u
        JOIN   roles r ON r.id = u.role_id
        LEFT JOIN interns i ON i.user_id = u.id
        WHERE  u.id = %s
        LIMIT  1
        """,
        (user_id,),
    )
    row = cur.fetchone()
    conn.close()
    if row is None:
        return {"error": f"User {user_id} not found"}

    ctx = {
        "user_id": row["id"],
        "full_name": row["full_name"],
        "email": row["email"],
        "role": row["role_name"],
        "intern_id": row["intern_id"],  # None if not a Stagiaire
    }
    return ctx


# ─────────────────────────────────────────────────────────────────────────────
# Scoped queries — return data the user is ALLOWED to see
# ─────────────────────────────────────────────────────────────────────────────

def list_interns(user_id: int, role: str, intern_id: Optional[int] = None) -> list[dict]:
    """List interns visible to this user."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)

    if role == "Stagiaire":
        # Intern can only see themselves
        if intern_id is None:
            conn.close()
            return []
        cur.execute(
            """
            SELECT i.id, u.full_name, u.email, i.school, i.specialty,
                   i.start_date, i.end_date, i.is_archived
            FROM   interns i JOIN users u ON u.id = i.user_id
            WHERE  i.id = %s
            """,
            (intern_id,),
        )
    elif role == "Encadrant":
        # Encadrant sees only their supervised interns
        cur.execute(
            """
            SELECT DISTINCT i.id, u.full_name, u.email, i.school, i.specialty,
                   i.start_date, i.end_date, i.is_archived
            FROM   interns i
            JOIN   users u ON u.id = i.user_id
            JOIN   internship_intern ii ON ii.intern_id = i.id
            JOIN   internships inv ON inv.id = ii.internship_id
            WHERE  inv.supervisor_id = %s
            ORDER  BY u.full_name
            """,
            (user_id,),
        )
    else:
        # Admin, RC, RH — see all
        cur.execute(
            """
            SELECT i.id, u.full_name, u.email, i.school, i.specialty,
                   i.start_date, i.end_date, i.is_archived
            FROM   interns i JOIN users u ON u.id = i.user_id
            ORDER  BY u.full_name
            """
        )

    rows = _safe_rows(cur)
    conn.close()
    return rows


def list_internships(user_id: int, role: str, intern_id: Optional[int] = None) -> list[dict]:
    """List internships visible to this user."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)

    if role == "Stagiaire":
        if intern_id is None:
            conn.close()
            return []
        cur.execute(
            """
            SELECT inv.id, inv.title, inv.description, inv.department,
                   inv.status, inv.start_date, inv.end_date,
                   sup.full_name AS supervisor_name
            FROM   internships inv
            JOIN   internship_intern ii ON ii.internship_id = inv.id
            LEFT JOIN users sup ON sup.id = inv.supervisor_id
            WHERE  ii.intern_id = %s
            """,
            (intern_id,),
        )
    elif role == "Encadrant":
        cur.execute(
            """
            SELECT inv.id, inv.title, inv.description, inv.department,
                   inv.status, inv.start_date, inv.end_date,
                   sup.full_name AS supervisor_name,
                   GROUP_CONCAT(u2.full_name SEPARATOR ', ') AS intern_names
            FROM   internships inv
            LEFT JOIN users sup ON sup.id = inv.supervisor_id
            LEFT JOIN internship_intern ii ON ii.internship_id = inv.id
            LEFT JOIN interns i2 ON i2.id = ii.intern_id
            LEFT JOIN users u2 ON u2.id = i2.user_id
            WHERE  inv.supervisor_id = %s
            GROUP BY inv.id
            ORDER BY inv.start_date DESC
            """,
            (user_id,),
        )
    else:
        cur.execute(
            """
            SELECT inv.id, inv.title, inv.description, inv.department,
                   inv.status, inv.start_date, inv.end_date,
                   sup.full_name AS supervisor_name,
                   GROUP_CONCAT(u2.full_name SEPARATOR ', ') AS intern_names
            FROM   internships inv
            LEFT JOIN users sup ON sup.id = inv.supervisor_id
            LEFT JOIN internship_intern ii ON ii.internship_id = inv.id
            LEFT JOIN interns i2 ON i2.id = ii.intern_id
            LEFT JOIN users u2 ON u2.id = i2.user_id
            GROUP BY inv.id
            ORDER BY inv.start_date DESC
            """
        )

    rows = _safe_rows(cur)
    conn.close()
    return rows


def list_tasks(user_id: int, role: str, intern_id: Optional[int] = None) -> list[dict]:
    """List tasks visible to this user."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)

    if role == "Stagiaire":
        cur.execute(
            """
            SELECT t.id, t.title, t.details, t.status, t.due_date,
                   t.weekly_comment, inv.title AS internship_title,
                   ab.full_name AS assigned_by_name
            FROM   tasks t
            JOIN   internships inv ON inv.id = t.internship_id
            LEFT JOIN users ab ON ab.id = t.assigned_by
            WHERE  t.assigned_to = %s
            ORDER  BY t.due_date ASC
            """,
            (user_id,),
        )
    elif role == "Encadrant":
        cur.execute(
            """
            SELECT t.id, t.title, t.details, t.status, t.due_date,
                   t.weekly_comment, inv.title AS internship_title,
                   atu.full_name AS assigned_to_name
            FROM   tasks t
            JOIN   internships inv ON inv.id = t.internship_id
            LEFT JOIN users atu ON atu.id = t.assigned_to
            WHERE  inv.supervisor_id = %s
            ORDER  BY t.due_date ASC
            """,
            (user_id,),
        )
    else:
        cur.execute(
            """
            SELECT t.id, t.title, t.details, t.status, t.due_date,
                   inv.title AS internship_title,
                   atu.full_name AS assigned_to_name,
                   ab.full_name AS assigned_by_name
            FROM   tasks t
            JOIN   internships inv ON inv.id = t.internship_id
            LEFT JOIN users atu ON atu.id = t.assigned_to
            LEFT JOIN users ab ON ab.id = t.assigned_by
            ORDER  BY t.due_date ASC
            """
        )

    rows = _safe_rows(cur)
    conn.close()
    return rows


def list_absences(user_id: int, role: str, intern_id: Optional[int] = None) -> list[dict]:
    """List absences visible to this user."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)

    if role == "Stagiaire":
        if intern_id is None:
            conn.close()
            return []
        cur.execute(
            """
            SELECT a.id, a.date_absence, a.reason, a.justified
            FROM   absences a
            WHERE  a.intern_id = %s
            ORDER  BY a.date_absence DESC
            """,
            (intern_id,),
        )
    elif role == "Encadrant":
        cur.execute(
            """
            SELECT a.id, a.date_absence, a.reason, a.justified,
                   u.full_name AS intern_name
            FROM   absences a
            JOIN   interns i ON i.id = a.intern_id
            JOIN   users u ON u.id = i.user_id
            JOIN   internship_intern ii ON ii.intern_id = i.id
            JOIN   internships inv ON inv.id = ii.internship_id
            WHERE  inv.supervisor_id = %s
            ORDER  BY a.date_absence DESC
            """,
            (user_id,),
        )
    else:
        cur.execute(
            """
            SELECT a.id, a.date_absence, a.reason, a.justified,
                   u.full_name AS intern_name
            FROM   absences a
            JOIN   interns i ON i.id = a.intern_id
            JOIN   users u ON u.id = i.user_id
            ORDER  BY a.date_absence DESC
            """
        )

    rows = _safe_rows(cur)
    conn.close()
    return rows


def get_stats(user_id: int, role: str, intern_id: Optional[int] = None) -> dict:
    """Return aggregate statistics scoped to the user's permissions."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)

    stats = {}

    if role == "Stagiaire":
        # Only their own stats
        cur.execute("SELECT COUNT(*) AS cnt FROM tasks WHERE assigned_to = %s", (user_id,))
        stats["total_tasks"] = cur.fetchone()["cnt"]
        cur.execute("SELECT COUNT(*) AS cnt FROM tasks WHERE assigned_to = %s AND status = 'termine'", (user_id,))
        stats["completed_tasks"] = cur.fetchone()["cnt"]
        cur.execute("SELECT COUNT(*) AS cnt FROM tasks WHERE assigned_to = %s AND status = 'en_cours'", (user_id,))
        stats["in_progress_tasks"] = cur.fetchone()["cnt"]
        if intern_id:
            cur.execute("SELECT COUNT(*) AS cnt FROM absences WHERE intern_id = %s", (intern_id,))
            stats["total_absences"] = cur.fetchone()["cnt"]
            cur.execute("SELECT COUNT(*) AS cnt FROM absences WHERE intern_id = %s AND justified = 0", (intern_id,))
            stats["unjustified_absences"] = cur.fetchone()["cnt"]
    elif role == "Encadrant":
        cur.execute(
            """SELECT COUNT(DISTINCT ii.intern_id) AS cnt
               FROM   internship_intern ii
               JOIN   internships inv ON inv.id = ii.internship_id
               WHERE  inv.supervisor_id = %s""",
            (user_id,),
        )
        stats["total_interns"] = cur.fetchone()["cnt"]
        cur.execute(
            """SELECT COUNT(*) AS cnt
               FROM   internships WHERE supervisor_id = %s""",
            (user_id,),
        )
        stats["total_internships"] = cur.fetchone()["cnt"]
        cur.execute(
            """SELECT COUNT(*) AS cnt
               FROM   tasks t JOIN internships inv ON inv.id = t.internship_id
               WHERE  inv.supervisor_id = %s""",
            (user_id,),
        )
        stats["total_tasks"] = cur.fetchone()["cnt"]
        cur.execute(
            """SELECT COUNT(*) AS cnt
               FROM   tasks t JOIN internships inv ON inv.id = t.internship_id
               WHERE  inv.supervisor_id = %s AND t.status = 'termine'""",
            (user_id,),
        )
        stats["completed_tasks"] = cur.fetchone()["cnt"]
    else:
        cur.execute("SELECT COUNT(*) AS cnt FROM interns WHERE is_archived = 0")
        stats["active_interns"] = cur.fetchone()["cnt"]
        cur.execute("SELECT COUNT(*) AS cnt FROM internships")
        stats["total_internships"] = cur.fetchone()["cnt"]
        cur.execute("SELECT COUNT(*) AS cnt FROM tasks")
        stats["total_tasks"] = cur.fetchone()["cnt"]
        cur.execute("SELECT COUNT(*) AS cnt FROM tasks WHERE status = 'termine'")
        stats["completed_tasks"] = cur.fetchone()["cnt"]
        cur.execute("SELECT COUNT(*) AS cnt FROM absences WHERE justified = 0")
        stats["unjustified_absences"] = cur.fetchone()["cnt"]

    conn.close()
    return stats


def search_intern_by_name(name: str, user_id: int, role: str) -> list[dict]:
    """Search for an intern by name, scoped to user permissions."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    pattern = f"%{name}%"

    if role == "Encadrant":
        cur.execute(
            """
            SELECT DISTINCT i.id, u.full_name, u.email, i.school, i.specialty,
                   i.start_date, i.end_date
            FROM   interns i
            JOIN   users u ON u.id = i.user_id
            JOIN   internship_intern ii ON ii.intern_id = i.id
            JOIN   internships inv ON inv.id = ii.internship_id
            WHERE  inv.supervisor_id = %s AND u.full_name LIKE %s
            """,
            (user_id, pattern),
        )
    elif role == "Stagiaire":
        # Can only find themselves
        cur.execute(
            """
            SELECT i.id, u.full_name, u.email, i.school, i.specialty
            FROM   interns i JOIN users u ON u.id = i.user_id
            WHERE  u.id = %s AND u.full_name LIKE %s
            """,
            (user_id, pattern),
        )
    else:
        cur.execute(
            """
            SELECT i.id, u.full_name, u.email, i.school, i.specialty,
                   i.start_date, i.end_date
            FROM   interns i JOIN users u ON u.id = i.user_id
            WHERE  u.full_name LIKE %s
            """,
            (pattern,),
        )

    rows = _safe_rows(cur)
    conn.close()
    return rows


# ─────────────────────────────────────────────────────────────────────────────
# Chat persistence — sessions & messages
# ─────────────────────────────────────────────────────────────────────────────

def create_chat_session(user_id: int, model: Optional[str] = None) -> int:
    """Create a new chat session and return its ID."""
    conn = _get_conn()
    cur = conn.cursor()
    cur.execute(
        "INSERT INTO chat_sessions (user_id, model, created_at, updated_at) VALUES (%s, %s, NOW(), NOW())",
        (user_id, model),
    )
    conn.commit()
    session_id = cur.lastrowid
    conn.close()
    log.info("Created chat session %d for user %d", session_id, user_id)
    return session_id


def get_chat_session(session_id: int) -> Optional[dict]:
    """Return a chat session by ID."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute("SELECT * FROM chat_sessions WHERE id = %s LIMIT 1", (session_id,))
    row = cur.fetchone()
    conn.close()
    if row:
        return {k: _safe_val(v) for k, v in row.items()}
    return None


def update_session_model(session_id: int, model: str) -> None:
    """Update the model associated with a chat session."""
    conn = _get_conn()
    cur = conn.cursor()
    cur.execute(
        "UPDATE chat_sessions SET model = %s, updated_at = NOW() WHERE id = %s",
        (model, session_id),
    )
    conn.commit()
    conn.close()


def update_session_title(session_id: int, title: str) -> None:
    """Update the title of a chat session."""
    conn = _get_conn()
    cur = conn.cursor()
    cur.execute(
        "UPDATE chat_sessions SET title = %s, updated_at = NOW() WHERE id = %s",
        (title[:255], session_id),
    )
    conn.commit()
    conn.close()


def save_chat_message(session_id: int, role: str, content: str) -> int:
    """Persist a message (user or assistant) and return its ID."""
    conn = _get_conn()
    cur = conn.cursor()
    cur.execute(
        "INSERT INTO chat_messages (session_id, role, content, created_at, updated_at) VALUES (%s, %s, %s, NOW(), NOW())",
        (session_id, role, content),
    )
    conn.commit()
    msg_id = cur.lastrowid
    # Also touch the session's updated_at
    cur.execute("UPDATE chat_sessions SET updated_at = NOW() WHERE id = %s", (session_id,))
    conn.commit()
    conn.close()
    return msg_id


def load_chat_messages(session_id: int, limit: int = 50) -> list[dict]:
    """Load messages for a chat session, ordered chronologically."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT id, role, content, created_at
        FROM   chat_messages
        WHERE  session_id = %s
        ORDER  BY created_at ASC
        LIMIT  %s
        """,
        (session_id, limit),
    )
    rows = _safe_rows(cur)
    conn.close()
    return rows


def list_chat_sessions(user_id: int, limit: int = 20) -> list[dict]:
    """Return the user's recent chat sessions (most recent first)."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT cs.id, cs.title, cs.model, cs.created_at, cs.updated_at,
               (SELECT COUNT(*) FROM chat_messages WHERE session_id = cs.id) AS message_count
        FROM   chat_sessions cs
        WHERE  cs.user_id = %s
        ORDER  BY cs.updated_at DESC
        LIMIT  %s
        """,
        (user_id, limit),
    )
    rows = _safe_rows(cur)
    conn.close()
    return rows


def delete_chat_session(session_id: int) -> None:
    """Delete a chat session and all its messages."""
    conn = _get_conn()
    cur = conn.cursor()
    cur.execute("DELETE FROM chat_sessions WHERE id = %s", (session_id,))
    conn.commit()
    conn.close()


# ─────────────────────────────────────────────────────────────────────────────
# Admin-only write operations
# ─────────────────────────────────────────────────────────────────────────────

def admin_send_message(sender_id: int, receiver_email: str, subject: str, body: str) -> dict:
    """
    Send an internal message from the admin to another user.
    Returns the created message details or an error.
    """
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)

    # Resolve receiver by email
    cur.execute("SELECT id, full_name FROM users WHERE email = %s LIMIT 1", (receiver_email,))
    receiver = cur.fetchone()
    if not receiver:
        conn.close()
        return {"error": f"Utilisateur avec l'email '{receiver_email}' introuvable."}

    cur.execute(
        """INSERT INTO messages (sender_id, receiver_id, subject, body, created_at, updated_at)
           VALUES (%s, %s, %s, %s, NOW(), NOW())""",
        (sender_id, receiver["id"], subject, body),
    )
    conn.commit()
    msg_id = cur.lastrowid
    conn.close()

    log.info("Admin %d sent message #%d to %s (%s)", sender_id, msg_id, receiver["full_name"], receiver_email)
    return {
        "success": True,
        "message_id": msg_id,
        "receiver_name": receiver["full_name"],
        "receiver_email": receiver_email,
        "subject": subject,
    }


def admin_send_message_by_name(sender_id: int, receiver_name: str, subject: str, body: str) -> dict:
    """
    Send an internal message from the admin to a user looked up by name.
    """
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)

    cur.execute(
        "SELECT id, full_name, email FROM users WHERE full_name LIKE %s LIMIT 5",
        (f"%{receiver_name}%",),
    )
    matches = cur.fetchall()
    conn.close()

    if not matches:
        return {"error": f"Aucun utilisateur trouvé avec le nom '{receiver_name}'."}
    if len(matches) > 1:
        names = [f"{m['full_name']} ({m['email']})" for m in matches]
        return {"error": f"Plusieurs utilisateurs trouvés : {', '.join(names)}. Précisez l'email."}

    return admin_send_message(sender_id, matches[0]["email"], subject, body)


def admin_list_all_users() -> list[dict]:
    """List all users with their roles (admin only)."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT u.id, u.full_name, u.email, u.is_active,
               r.name AS role_name
        FROM   users u
        JOIN   roles r ON r.id = u.role_id
        ORDER  BY r.name, u.full_name
        """
    )
    rows = _safe_rows(cur)
    conn.close()
    return rows


def admin_list_users_by_role(role_name: str) -> list[dict]:
    """List all users with a specific role (admin only)."""
    conn = _get_conn()
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT u.id, u.full_name, u.email, u.is_active
        FROM   users u
        JOIN   roles r ON r.id = u.role_id
        WHERE  r.name = %s
        ORDER  BY u.full_name
        """,
        (role_name,),
    )
    rows = _safe_rows(cur)
    conn.close()
    return rows
