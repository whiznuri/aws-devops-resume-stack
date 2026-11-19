# L-08 Authentication + RBAC

```mermaid
sequenceDiagram
  participant B as Browser
  participant P as Next.js proxy.ts
  participant N as Next.js (Server Action / page)
  participant A as FastAPI
  participant D as PostgreSQL
  B->>N: login form
  N->>A: POST /auth/login
  A->>D: user by e-mail, Argon2 verify, is_active
  A->>D: INSERT refresh_tokens (SHA-256 hash, last_used_at, session_expires_at)
  A-->>N: access JWT (15 min, sub + role) + refresh token
  N-->>B: Set-Cookie (httpOnly, SameSite=Lax, Secure)
  Note over B,P: later — access cookie has expired
  B->>P: any request (not a prefetch)
  P->>A: POST /auth/refresh
  A->>D: SELECT token FOR UPDATE · revoked? account disabled? past absolute limit? idle too long?
  alt valid
    A->>D: revoke old token, issue new one (same absolute limit) — rotation
    P-->>B: new cookies, request continues
  else invalid
    P-->>B: cookies cleared (logged out)
  end
  B->>N: GET /backoffice
  N->>A: GET /auth/me
  alt not staff/admin
    N-->>B: redirect /?notice=backoffice-denied (lock 1)
  else
    N->>A: GET /admin/... + Bearer
    A->>A: require_roles() on the JWT claims (lock 2) → 401 / 403 / ok
  end
```

| Role | Can |
|---|---|
| customer | shop, own cart and orders |
| staff | back office: products, stock, orders |
| admin | everything (superuser) + members, dashboard |

| Policy | customer | staff / admin |
|---|---|---|
| Idle timeout (sliding) | none | 30 min |
| Absolute session limit | 7 days | 8 hours |

- Role checks read the JWT only (no DB hit); a disabled account loses access within one access-token lifetime.
- `/admin/users` returns other members' e-mails masked; the requesting admin sees their own in full.
- The hidden back-office button is UX only; the real locks are the page guard and the API dependency.
