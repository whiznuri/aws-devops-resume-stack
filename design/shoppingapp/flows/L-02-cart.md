# L-02 Cart

```mermaid
sequenceDiagram
  participant B as Browser
  participant N as Next.js (Server Action)
  participant A as FastAPI (auth required)
  participant D as PostgreSQL
  B->>N: "Add to cart" (variant_id)
  alt not logged in
    N-->>B: redirect /login?next=/products/{id} (returns to the product after login)
  else logged in
    N->>A: POST /cart/items {variant_id, quantity} + Bearer JWT
    A->>D: read variant (active? stock ≥ new quantity? ≤ 10?)
    alt rule violated
      A-->>N: 409 "สินค้าหมด" / "สินค้ามีไม่พอ" / "ไม่มีจำหน่ายแล้ว" (no numbers)
    else ok
      A->>D: insert or increment (UNIQUE user_id, variant_id)
      A-->>N: cart view
    end
    N-->>B: message + refreshed cart badge
  end
  B->>N: GET /cart
  N->>A: GET /cart
  A->>D: cart → variant → product → images (chained selectinload, no N+1)
  A-->>N: lines with status ok / insufficient / sold_out / unavailable, subtotal of buyable lines, can_checkout
```

| Rule | Why |
|---|---|
| The cart never reserves stock | prevents inventory hoarding (denial of inventory); stock is taken at checkout |
| Login required | quantities are tied to an identified user |
| `user_id` from the JWT only | prevents IDOR (editing another user's cart) |
| Prices are always current | the price is locked when the order is created (L-03) |
| Lines re-evaluated on every read | items can sell out or be suspended after being added |
| `?next=` must be an internal path | open-redirect guard (`//evil.com` → `/`) |
