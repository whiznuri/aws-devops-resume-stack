# L-03 Checkout

The core transaction: deduct stock and create the order atomically.

```mermaid
sequenceDiagram
  participant B as Browser
  participant N as Next.js (Server Action)
  participant A as FastAPI
  participant D as PostgreSQL
  B->>N: confirm order (name, phone, address)
  N->>A: POST /orders + Bearer JWT
  A->>D: BEGIN
  A->>D: SELECT cart_items FOR UPDATE (user's rows)
  alt cart empty (e.g. double click — first request already cleared it)
    A-->>N: 409 "ตะกร้าว่าง"
  else
    A->>D: SELECT product_variants FOR UPDATE ORDER BY id
    alt any line inactive / sold out / insufficient
      A->>D: ROLLBACK (nothing changes, cart intact)
      A-->>N: 409 {problems: {sku: reason}}
    else all buyable
      A->>D: UPDATE stock -= qty · INSERT order (pending_payment, expires_at = now + 60 min) · INSERT order_items (snapshot) · DELETE cart_items
      A->>D: COMMIT
      A-->>N: 201 order
      N-->>B: redirect /orders/{id}
    end
  end
```

| Concern | Approach |
|---|---|
| Overselling | row locks on variants; a two-thread race test proves only one buyer gets the last item |
| Deadlocks | every transaction locks variants in ascending id order |
| Partial orders | all-or-nothing; the customer fixes the cart and retries |
| Price changes later | `order_items` stores name, SKU, colour, size and unit price at purchase time |
| Duplicate submissions | cart rows are locked and deleted in the same transaction; the second request finds an empty cart |
| Visibility | owner only; other users get 404 (does not confirm the id exists) |
| Shipping data | owner sees it in full; staff views must mask it (team masking standard) |

Settings: `ORDER_PAYMENT_WINDOW_MINUTES` (default 60).
