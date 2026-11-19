# Customer journey (swimlane)

Business-level view for non-technical readers. Each step carries the flow ID so the team can jump to the technical diagram.

```mermaid
flowchart TB
  subgraph CU["👤 Customer"]
    A1["Browse & search<br/>L-01"] --> A2["Pick colour / size<br/>L-01"] --> A3["Log in<br/>L-08"] --> A4["Add to cart<br/>L-02"] --> A5["Checkout + shipping address<br/>L-03"]
    A6["Scan PromptPay QR<br/>L-04"]
  end
  subgraph SY["⚙️ Store system"]
    S1{"All items<br/>available?"}
    S0["Show which items<br/>can't be bought"]
    S2["Reserve stock 1 hour<br/>lock prices"]
    S3{"Paid in full?<br/>(checked with Omise)"}
    S4["Order paid"]
    S5["After 1 hour unpaid:<br/>expire + release stock<br/>L-07"]
  end
  subgraph ST["🧑‍💼 Staff / Admin"]
    D1["Manage products,<br/>variants, images<br/>L-09"]
  end
  D1 -. "what customers see" .-> A1
  A5 --> S1
  S1 -- "no" --> S0 --> A4
  S1 -- "yes" --> S2 --> A6 --> S3
  S3 -- "yes" --> S4
  S3 -- "not yet" --> A6
  S2 -. "timer" .-> S5
```

Legend: lanes show who acts · ◇ decisions · dashed lines happen without anyone clicking.
