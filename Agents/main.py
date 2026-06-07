"""
FastAPI entry-point for the Encadrant Copilot agent microservice.

Endpoints:
  GET  /health                  — liveness probe
  POST /api/weekly-summary      — trigger the LangGraph workflow
  GET  /api/weekly-summary/{id} — convenience GET (week defaults to previous week)
"""
from __future__ import annotations

import logging
import os
import time
from datetime import date
from typing import Optional

import uvicorn
from dotenv import load_dotenv
from fastapi import FastAPI, HTTPException, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from pydantic import BaseModel

from agent.graph import copilot_graph

load_dotenv()

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s %(message)s",
    datefmt="%H:%M:%S",
)
log = logging.getLogger("api")

# ─────────────────────────────────────────────────────────────────────────────
# App
# ─────────────────────────────────────────────────────────────────────────────

app = FastAPI(
    title="Encadrant Copilot — Weekly Summary Agent",
    description="LangGraph + LangChain agent that generates weekly intern reports.",
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.middleware("http")
async def log_requests(request: Request, call_next):
    t0 = time.perf_counter()
    log.info("⬇️   %s %s", request.method, request.url.path)
    response = await call_next(request)
    elapsed = time.perf_counter() - t0
    log.info("⬆️   %s %s → %d  (%.2fs)", request.method, request.url.path, response.status_code, elapsed)
    return response


# ─────────────────────────────────────────────────────────────────────────────
# Schemas
# ─────────────────────────────────────────────────────────────────────────────

class SummaryRequest(BaseModel):
    intern_id: int
    week_start: Optional[str] = None   # YYYY-MM-DD; defaults to previous Monday
    week_end: Optional[str] = None     # YYYY-MM-DD; defaults to previous Sunday


class SummaryResponse(BaseModel):
    success: bool
    intern_id: int
    week_start: str
    week_end: str
    report: Optional[dict] = None
    error: Optional[str] = None


# ─────────────────────────────────────────────────────────────────────────────
# Endpoints
# ─────────────────────────────────────────────────────────────────────────────

@app.get("/health", tags=["System"])
def health():
    return {"status": "ok", "service": "encadrant-copilot"}


@app.post("/api/weekly-summary", response_model=SummaryResponse, tags=["Agent"])
def generate_weekly_summary(req: SummaryRequest):
    """
    Trigger the full LangGraph workflow for the given intern and week.
    If week_start / week_end are omitted, defaults to the previous week.
    """
    initial_state = {
        "intern_id": req.intern_id,
        "week_start": req.week_start or "",
        "week_end": req.week_end or "",
        "intern_info": None,
        "internship_info": None,
        "tasks": None,
        "messages": None,
        "daily_logs": None,
        "task_analysis": None,
        "sentiment_analysis": None,
        "report": None,
        "error": None,
    }

    try:
        final_state = copilot_graph.invoke(initial_state)
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc))

    if final_state.get("error"):
        return SummaryResponse(
            success=False,
            intern_id=req.intern_id,
            week_start=final_state.get("week_start", ""),
            week_end=final_state.get("week_end", ""),
            error=final_state["error"],
        )

    return SummaryResponse(
        success=True,
        intern_id=req.intern_id,
        week_start=final_state["week_start"],
        week_end=final_state["week_end"],
        report=final_state.get("report"),
    )


@app.get("/api/weekly-summary/{intern_id}", response_model=SummaryResponse, tags=["Agent"])
def get_weekly_summary(
    intern_id: int,
    week_start: Optional[str] = None,
    week_end: Optional[str] = None,
):
    """Convenience GET endpoint — same behaviour as POST but via query params."""
    return generate_weekly_summary(
        SummaryRequest(intern_id=intern_id, week_start=week_start, week_end=week_end)
    )


# ─────────────────────────────────────────────────────────────────────────────
# Chat API
# ─────────────────────────────────────────────────────────────────────────────

# ─────────────────────────────────────────────────────────────────────────────
# Chat API
# ─────────────────────────────────────────────────────────────────────────────

from agent.chat_agent import chat as chat_fn
from agent.chat_db import (
    create_chat_session,
    delete_chat_session,
    list_chat_sessions,
    load_chat_messages,
    update_session_model,
)

class ChatRequest(BaseModel):
    user_id: int
    user_role: str
    message: str
    session_id: Optional[int] = None
    model: Optional[str] = None

class ChatResponse(BaseModel):
    success: bool
    reply: Optional[str] = None
    session_id: int
    model_used: str
    error: Optional[str] = None


@app.post("/api/chat", response_model=ChatResponse, tags=["Chat"])
def chat_endpoint(req: ChatRequest):
    """Send a message to the AI assistant. The response is scoped by user role."""
    
    # Resolve model
    default_model = os.getenv("CHAT_DEFAULT_MODEL", "deepseek/deepseek-v4-flash")
    available_raw = os.getenv("CHAT_MODELS", default_model)
    available = [m.strip() for m in available_raw.split(",") if m.strip()]
    model = req.model if (req.model and req.model in available) else default_model

    # Resolve or create session
    if req.session_id:
        session_id = req.session_id
        update_session_model(session_id, model)
    else:
        session_id = create_chat_session(req.user_id, model)

    try:
        reply = chat_fn(
            user_id=req.user_id,
            message=req.message,
            session_id=session_id,
            model=model,
        )
        return ChatResponse(
            success=True,
            reply=reply,
            session_id=session_id,
            model_used=model,
        )
    except Exception as exc:
        log.exception("Chat error")
        return ChatResponse(
            success=False,
            error=str(exc),
            session_id=session_id,
            model_used=model,
        )


@app.get("/api/chat/models", tags=["Chat"])
def chat_models():
    """Return the list of available chat models."""
    default_model = os.getenv("CHAT_DEFAULT_MODEL", "deepseek/deepseek-v4-flash")
    available_raw = os.getenv("CHAT_MODELS", default_model)
    models = [m.strip() for m in available_raw.split(",") if m.strip()]
    return {"models": models, "default": default_model}


@app.get("/api/chat/sessions/{user_id}", tags=["Chat"])
def get_chat_sessions(user_id: int):
    """List recent chat sessions for a user."""
    sessions = list_chat_sessions(user_id)
    return {"sessions": sessions}


@app.get("/api/chat/session/{session_id}/history", tags=["Chat"])
def get_chat_history(session_id: int):
    """Load messages for a specific session."""
    messages = load_chat_messages(session_id)
    return {"messages": messages}


@app.delete("/api/chat/session/{session_id}", tags=["Chat"])
def chat_clear_session(session_id: int):
    """Clear the conversation history for a session."""
    delete_chat_session(session_id)
    return {"success": True, "message": "Session deleted."}


# ─────────────────────────────────────────────────────────────────────────────
# Run
# ─────────────────────────────────────────────────────────────────────────────

if __name__ == "__main__":
    uvicorn.run(
        "main:app",
        host=os.getenv("FASTAPI_HOST", "0.0.0.0"),
        port=int(os.getenv("FASTAPI_PORT", 8001)),
        reload=True,
    )

