"""
LangGraph graph definition for the Encadrant Copilot weekly summary agent.

Graph topology (sequential):

  validate_input
       │
       ▼
  fetch_data  ──(error?)──► END
       │
       ▼
  analyze_tasks
       │
       ▼
  analyze_sentiment
       │
       ▼
  generate_report
       │
       ▼
      END
"""
from langgraph.graph import StateGraph, END

from agent.state import WeeklySummaryState
from agent.nodes import (
    analyze_sentiment,
    analyze_tasks,
    fetch_data,
    generate_report,
    validate_input,
)


def _has_error(state: WeeklySummaryState) -> str:
    """Conditional edge: short-circuit to END when an error is present."""
    return "end" if state.get("error") else "continue"


# ─────────────────────────────────────────────────────────────────────────────
# Build the graph
# ─────────────────────────────────────────────────────────────────────────────

def build_graph() -> StateGraph:
    workflow = StateGraph(WeeklySummaryState)

    # Register nodes
    workflow.add_node("validate_input", validate_input)
    workflow.add_node("fetch_data", fetch_data)
    workflow.add_node("analyze_tasks", analyze_tasks)
    workflow.add_node("analyze_sentiment", analyze_sentiment)
    workflow.add_node("generate_report", generate_report)

    # Entry point
    workflow.set_entry_point("validate_input")

    # Edges
    workflow.add_conditional_edges(
        "validate_input",
        _has_error,
        {"end": END, "continue": "fetch_data"},
    )
    workflow.add_conditional_edges(
        "fetch_data",
        _has_error,
        {"end": END, "continue": "analyze_tasks"},
    )
    workflow.add_edge("analyze_tasks", "analyze_sentiment")
    workflow.add_edge("analyze_sentiment", "generate_report")
    workflow.add_edge("generate_report", END)

    return workflow.compile()


# Singleton compiled graph (reused across requests)
copilot_graph = build_graph()
