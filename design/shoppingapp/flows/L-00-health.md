# L-00 Health check

Proves every tier is wired: browser → Next.js server → FastAPI → PostgreSQL.

```mermaid
sequenceDiagram
  participant B as Browser
  participant N as Next.js (Server Component)
  participant A as FastAPI
  participant D as PostgreSQL
  B->>N: GET /status
  N->>A: GET /health (internal URL, never exposed to the browser)
  A->>D: SELECT 1
  alt DB reachable
    D-->>A: 1
    A-->>N: {"api":"ok","db":"ok"}
  else DB down
    A-->>N: {"api":"ok","db":"down"} (still HTTP 200 so the page can show which tier failed)
  end
  N-->>B: three status lamps
```

- `connection()` makes the page render per request (not at build time).
- Session-per-request: `get_session()` yields one SQLAlchemy session per request and always closes it.
- Tests: all-ok against the real DB; DB-down via a dependency override that raises `OperationalError`.
