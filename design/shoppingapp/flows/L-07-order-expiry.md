# L-07 Order expiry (background job)

```mermaid
flowchart LR
  J["Timer thread<br/>(FastAPI lifespan, every 60 s)"] --> Q["SELECT pending_payment orders<br/>WHERE expires_at <= now<br/>FOR UPDATE SKIP LOCKED"]
  Q --> P{"Pending QR?"}
  P -- "no" --> E["status = expired<br/>release stock<br/>(lock variants by id)"]
  P -- "yes" --> O{{"Omise: GET charge"}}
  O -- "unreachable" --> S["skip, retry next run"]
  O -- "paid in full" --> PD["status = paid"]
  O -- "not paid" --> E
```

| Decision | Reason |
|---|---|
| Check Omise before expiring | a customer who paid in the last minute must not lose the order |
| Unreachable provider → skip | when unsure, do not cancel |
| `SKIP LOCKED` | safe with several API replicas; never blocks on a row a customer is checking |
| Thread in the API process | no extra container on a 1 GB instance |
| Shared `apply_charge()` | one implementation of the "paid correctly" rule for both the check button and the job |

Settings: `EXPIRE_JOB_ENABLED` (disabled in tests), `EXPIRE_JOB_INTERVAL_SECONDS`.
Manual run: `docker compose exec shopping-api python -m app.scripts.expire_orders`.
Known gap: a QR paid after expiry needs a manual refund.
