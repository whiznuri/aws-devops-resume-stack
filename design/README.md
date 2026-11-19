# Design docs

Architecture and feature documentation for the apps in this repository.

| Folder | What it covers |
|---|---|
| [`shoppingapp/`](shoppingapp/README.md) | Shopping App (`apps/shoppingapp-next`): architecture, features, business rules, data model, per-feature flow diagrams, customer journey, [roadmap](shoppingapp/ROADMAP.md), deployment runbook |

## How the diagrams are organised

Each feature is documented as one **end-to-end flow** (a vertical slice: UI → BFF → API → DB and back), numbered `L-xx`. The [feature index](shoppingapp/README.md#feature-index) maps every feature to its flow, endpoints, tables, and tests, so a change request can be traced to the exact slice it touches (requirements traceability).

Diagrams are [Mermaid](https://mermaid.js.org/) and render on GitHub.

## Where the `L-xx` numbering comes from

The method is borrowed from industrial instrumentation, where every control loop has a tag number, a loop drawing and a loop test before commissioning. The software equivalents used here:

| Instrumentation practice | Software term | In this repo |
|---|---|---|
| Loop drawing (one loop, end to end) | Sequence diagram per feature / end-to-end flow (vertical slice) | [`shoppingapp/flows/`](shoppingapp/flows) |
| Instrument / loop index | Feature index (traceability matrix) | [Feature index](shoppingapp/README.md#feature-index) |
| Loop tag number | Feature / requirement ID (`L-xx`) | every flow file and test file |
| Swimlane | Swimlane diagram / customer journey map | [`shoppingapp/customer-journey.md`](shoppingapp/customer-journey.md) |
| Loop test before handover | End-to-end check on the running app + automated test suite | `apps/shoppingapp-next/api/tests/` |
| Drawing revision (Rev 1, Rev 2 …) | Version history | git log of each file |

A feature counts as done only after its flow diagram, code, automated tests and an end-to-end check on the running app are all complete.
