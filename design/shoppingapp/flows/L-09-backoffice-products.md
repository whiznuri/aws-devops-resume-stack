# L-09 Back office: products, variants, images

```mermaid
sequenceDiagram
  participant B as Browser (staff)
  participant N as Next.js
  participant A as FastAPI (require_roles staff)
  participant D as PostgreSQL
  B->>N: /backoffice/products?q=
  N->>A: GET /admin/products?q= (name or SKU, ILIKE)
  A->>D: products + selectinload variants/images
  A-->>N: list (incl. suspended, with stock numbers)
  B->>N: create product (dynamic variant rows)
  N->>A: POST /admin/products {name, price, variants[], images[]}
  A->>A: Pydantic: price ≥ 0 with 2 decimals, ≥ 1 variant, stock ≥ 0, unknown fields rejected
  A->>D: INSERT product + variants + images (one transaction)
  alt duplicate SKU (UNIQUE)
    A-->>N: 409
  else
    A-->>N: 201
  end
  B->>N: edit / suspend / add variant / add or remove image
  N->>A: PATCH /admin/products/{id} · POST /admin/products/{id}/variants · PATCH /admin/variants/{id} · POST /admin/products/{id}/images · DELETE /admin/images/{id}
```

| Rule | Implementation |
|---|---|
| No hard delete of products or variants | no DELETE route (405); suspend with `is_active=false` |
| Stock set only on creation | `VariantUpdate` has no `stock` field (`extra="forbid"` → 422); later changes go through restock (L-10, backlog) so every change is recorded |
| Money | `Numeric(10,2)` / `Decimal`, serialised as strings (`"390.00"`) |
| SKU | trimmed and upper-cased; uniqueness enforced by the database |
| Images | HTTPS URLs only, grouped by colour; images can be deleted (not transactional data) |

Seed: `python -m app.scripts.seed_products` creates 15 products / 89 variants with colour-matched placeholder images; idempotent.
