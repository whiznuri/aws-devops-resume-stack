# L-04 Payment (Omise PromptPay, test mode)

```mermaid
sequenceDiagram
  participant B as Browser
  participant N as Next.js (Server Action)
  participant A as FastAPI
  participant O as Omise API
  participant D as PostgreSQL
  B->>N: "Pay with PromptPay"
  N->>A: POST /orders/{id}/payment
  A->>D: order owned by user, pending_payment, not expired?
  alt a pending QR already exists
    A-->>N: same payment (no second charge)
  else
    A->>O: POST /charges {amount (satang), currency thb, source promptpay, metadata.order_id}
    O-->>A: charge id + QR image URL
    A->>D: INSERT payments
    A-->>N: QR
  end
  N-->>B: QR + "check status"
  B->>N: "check status"
  N->>A: POST /orders/{id}/payment/check
  A->>D: SELECT order FOR UPDATE
  A->>O: GET /charges/{charge_id}
  O-->>A: status, amount, currency, metadata
  alt successful AND amount == subtotal AND currency == THB AND order_id matches
    A->>D: payment successful · order paid
  else failed / expired
    A->>D: payment failed/expired (customer may request a new QR)
  else still pending
    A-->>N: still pending
  end
```

- **Never trust the client:** the browser only asks the server to check; the server asks Omise with the secret key.
- Amount, currency and order id are compared to block underpayment and replaying another order's charge.
- `OmiseClient` is a small `httpx` wrapper injected with `Depends(get_omise)`; tests swap in `FakeOmise`.
- Pull-based confirmation for now (localhost cannot receive webhooks); add a webhook endpoint once deployed.
- Test mode: simulate a customer payment with `POST /charges/{id}/mark_as_paid` (test keys only).
