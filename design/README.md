# Design docs

Architecture and feature documentation for the apps in this repository.

| Folder | What it covers |
|---|---|
| [`shoppingapp/`](shoppingapp/README.md) | Shopping App (`apps/shoppingapp-next`): architecture, features, business rules, data model, per-feature flow diagrams, customer journey, [roadmap](shoppingapp/ROADMAP.md), deployment runbook |

## How the diagrams are organised

Each feature is documented as one **end-to-end flow** (a vertical slice: UI → BFF → API → DB and back), numbered `L-xx`. The [feature index](shoppingapp/README.md#feature-index) maps every feature to its flow, endpoints, tables, and tests, so a change request can be traced to the exact slice it touches (requirements traceability).

Diagrams are [Mermaid](https://mermaid.js.org/) and render on GitHub.
