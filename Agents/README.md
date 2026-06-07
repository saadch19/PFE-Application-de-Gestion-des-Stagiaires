# 🤖 Encadrant Copilot — Weekly Summary Agent

A **LangGraph + LangChain + FastAPI** microservice that automatically generates
weekly intern progress reports for supervisors (Encadrants).

## Architecture

```
Laravel App (PHP)
      │  POST /ai/resume-hebdo/{intern}/generate
      │  (AiSummaryController proxies the request)
      ▼
FastAPI  (localhost:8001)
      │
      └─► LangGraph Graph:
            validate_input
                 │
            fetch_data  ──── MySQL (same DB as Laravel)
                 │
            analyze_tasks  ──── LLM via OpenRouter
                 │
            analyze_sentiment ── LLM via OpenRouter
                 │
            generate_report ──── LLM via OpenRouter
                 │
               END  ──► JSON report returned to Laravel
```

## Files

```
Agents/
├── .env               ← Fill in your credentials here
├── requirements.txt   ← Python dependencies
├── main.py            ← FastAPI entry-point
└── agent/
    ├── __init__.py
    ├── state.py       ← LangGraph TypedDict state
    ├── database.py    ← MySQL query helpers
    ├── nodes.py       ← Node functions (fetch, analyze, generate)
    └── graph.py       ← LangGraph StateGraph definition
```

## Setup

### 1. Fill in your `.env`

Open `Agents/.env` and set:

```env
OPENROUTER_API_KEY=sk-or-...          # Your OpenRouter API key
OPENROUTER_MODEL=anthropic/claude-3-haiku  # or openai/gpt-4o-mini etc.

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=pfe_stagiaires
DB_USER=pfe_user
DB_PASSWORD=your_mysql_password
```

### 2. Create a Python virtual environment

```powershell
cd Agents
python -m venv venv
venv\Scripts\activate
```

### 3. Install dependencies

```powershell
pip install -r requirements.txt
```

### 4. Start the FastAPI server

```powershell
python main.py
```

The server runs at **http://localhost:8001**.
Interactive API docs: http://localhost:8001/docs

## Usage

### Via the Laravel app (recommended)

1. Log in as **Encadrant**
2. Navigate to **Mes Stagiaires → [intern name]**
3. Click the **🤖 Résumé IA** button in the header
4. Select the week range and click **Générer le résumé**

### Direct API call

```bash
curl -X POST http://localhost:8001/api/weekly-summary \
  -H "Content-Type: application/json" \
  -d '{"intern_id": 1, "week_start": "2026-06-01", "week_end": "2026-06-07"}'
```

Or use the GET shortcut (defaults to previous week):
```bash
curl http://localhost:8001/api/weekly-summary/1
```

## How the intern feeds data to the agent

Interns can add a **weekly progress comment** to each of their tasks.
Go to **Tâches** (task list) — there is a textarea under each task for the intern
to write what they accomplished, any blockers they faced, etc.
Comments auto-save after 900ms of inactivity.

The agent reads these comments as part of its sentiment and task analysis.

## Report output

The final report includes:

| Field | Description |
|-------|-------------|
| `executive_summary` | 2-3 sentence overview |
| `achievements` | Bulleted list of completed work |
| `blockers` | Identified challenges |
| `sentiment_summary` | Intern wellbeing assessment |
| `recommended_actions` | Concrete steps for the supervisor |
| `overall_rating` | Excellent / Bien / Moyen / Nécessite attention |
| `week_score` | Numeric score 0–100 |
| `task_completion_rate` | % of tasks completed |
| `engagement_score` | 1–10 engagement level |
| `red_flags` | Alerts requiring urgent supervisor action |
| `overdue_tasks` | Tasks past their deadline |

## Recommended OpenRouter models

| Model | Speed | Quality | Cost |
|-------|-------|---------|------|
| `anthropic/claude-3-haiku` | Fast | High | Low |
| `openai/gpt-4o-mini` | Fast | High | Low |
| `meta-llama/llama-3.1-8b-instruct:free` | Medium | Medium | Free |
| `anthropic/claude-3.5-sonnet` | Medium | Excellent | Medium |
