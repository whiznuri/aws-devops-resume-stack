# L-01 Storefront

Public catalogue: grid with search and sort, product page with colour/size picker.

```mermaid
sequenceDiagram
  participant B as Browser
  participant N as Next.js
  participant A as FastAPI (public, no auth)
  participant D as PostgreSQL
  B->>N: GET /?q=&sort=
  N->>A: GET /products?q=&sort=new|price_asc|price_desc
  A->>D: active products having ≥ 1 active variant (EXISTS) + selectinload variants/images
  D-->>A: rows (inactive variants filtered by with_loader_criteria)
  A-->>N: cards {cover_url, colors, sizes, sold_out}
  N-->>B: product grid
  B->>N: GET /products/{id}
  N->>A: GET /products/{id}
  alt suspended or unknown
    A-->>N: 404
    N-->>B: not-found page
  else active
    A-->>N: images (grouped by colour) + variants with availability
    N-->>B: ProductView (Client Component): colour click swaps images, sizes show availability
  end
```

| Rule | Implementation |
|---|---|
| Never expose exact stock | `availability(stock)` → `in_stock` / `low_stock` (≤ `LOW_STOCK_THRESHOLD`, default 3) / `sold_out`; no `stock` field in the public schema |
| Suspended items disappear | filtered in SQL, not in the UI |
| Images follow colour | `product_images.color`; `NULL` = general/cover image |
| Search & sort are shareable | plain GET form, state lives in the URL |

Separate public endpoint (`/products`) instead of reusing `/admin/products`: hiding fields in the UI would still ship the data to the browser.
