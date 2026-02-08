# Production-grade Search & Autocomplete Engine

Laravel · Redis Streams · MeiliSearch · Event-Driven Architecture

---

## Architecture Diagram (Visual)

# Production-grade Search & Autocomplete Engine

Laravel · Redis Streams · MeiliSearch · Event-Driven Architecture

---

## Architecture Diagram (Visual)

```text
MySQL (PhpMyAdmin)
↓
Domain Layer
├─ Models
├─ Observers
├─ Dispatcher
└─ Pipelines
↓
Redis
├─ Streams (updates_stream, REDIS_STREAM_DB=3)
├─ Queues (REDIS_QUEUE_DB=2)
├─ Cache (REDIS_CACHE_DB=1)
└─ Sessions (REDIS_DB=0)
↓
Consumer Groups (ConsumeUpdates)
↓
Idempotent Locking (SETNX per doc, 5s lock)
↓
Index Brain (MeiliIntentRouter + Dependency Graph)
↓
Transformers (Brand/Product/Category/Seller/Shop)
↓
Document Builders & Batching
↓
Queue Layer (Horizon + Isolated Queues)
↓
MeiliSearch Indexes (autocomplete_brands, products, etc.)
↓
Search / Autocomplete API
↓
Frontend (React)
```

---

## This is NOT a search demo

This repository documents how a **real-world autocomplete system** is
**designed, evolved, and stress-tested under production constraints**.

Focus:
- architecture
- data flow
- trade-offs
- failure modes

Not frameworks.  
Not quick integrations.  
Not toy examples.

---

## Demo (real frontend, real data)

This short video shows how autocomplete behaves in practice:
- instant feedback
- multiple entity types
- predictable latency

Watch demo (frontend autocomplete in action): [https://youtu.be/t_PeFxUbrv4](https://youtu.be/t_PeFxUbrv4)

---

Laravel 12 project file structure demo: [https://youtu.be/WlKlPhlQNAU](https://youtu.be/WlKlPhlQNAU)

---

- Redis Insight: [https://youtu.be/DseAV2AoFbM](https://youtu.be/DseAV2AoFbM)
- Horizon/Meilisearch/PhpMyadmin: [https://youtu.be/aBYBvXBVono](https://youtu.be/aBYBvXBVono)

---

*(Full backend code is private. This repo focuses on architecture and logic.)*

---

## Problems this project addresses

- Preventing **reindex storms** under burst updates
- Keeping search data **consistent without full reindex**
- Scaling autocomplete across **multiple entity types**
- Avoiding **over-fetching and wasted ranking work**
- Keeping latency **predictable under load**

---

## Project Goal

This project demonstrates how **search infrastructure should be designed** —
not just “connect MeiliSearch to Laravel”.

Emphasis:
- correctness first
- explicit data flow
- debuggable behavior
- controlled failure recovery

---

## 🔑 Core Concepts

### Event-Driven Synchronization
- Database changes emit **explicit domain events**
- No polling
- No full reindex
- Every index update is traceable

### Redis Streams + Queues
- Persistent event log
- Reliable delivery
- Horizontal scalability
- Consumer groups with idempotent handling
- Locking prevents duplicate rebuilds

### Index Brain
- MeiliIntentRouter centralizes all logic
- Dependency graph resolves relations across entities
- Transformers construct final document
- Checks user/shop/product verification status

### Queue Isolation & Horizon
- Dedicated queues for indexing
- Heavy jobs isolated
- Throughput controlled via Horizon

### Declarative Relation Graph
- Search impact rules declared explicitly
- Deep relational graphs supported (4–6+ joins)
- No hidden Eloquent chains
- Transparent query behavior

### Search Index Design
- Transformers per document type
- Multilingual support
- Alias and normalization handling
- Separate autocomplete indexes (brands, products, sellers, shops, categories)

---

## Autocomplete System Modules

1. **Synchronization** – backend: from MySQL → Meili DB  
2. **Indexing** – backend: initial population + index configuration  
3. **Search & Results** – backend: query validation → autocomplete results  
4. **Frontend** – display results, filter UI, search field behavior

> Currently only **Synchronization** is fully demonstrated (most complex part)

---

## Source & Infrastructure Details

- **MySQL** (PhpMyAdmin)
- **Domain Layer**: Models, Observers, Dispatcher, Pipeline
- **Redis**: Streams, Queues, Cache, Sessions
  - REDIS_DB=0 (sessions)
  - REDIS_CACHE_DB=1 (cache)
  - REDIS_QUEUE_DB=2 (queues)
  - REDIS_STREAM_DB=3 (updates_stream)
- **Consumer Groups**: ConsumeUpdates
- **Locking**: SETNX, 5s locks per document
- **Index Brain**: MeiliIntentRouter + Dependency Graph
- **Transformers**: BrandTransformer, ProductTransformer, CategoryTransformer, SellerTransformer, ShopTransformer
- **Document Builders & Batching**
- **Queue Layer**: Horizon, Isolated indexing queues
- **Frontend**: React + autocomplete fields

---

## File Structure (Key Files)

```text
app/Services/Search/
├─ Registry/
│ └─ SearchSourceRegistry.php
├─ Infrastructure/
│ ├─ RedisStreamService.php
│ └─ RedisIndexer.php
├─ Intents/
│ ├─ SearchChannels.php
│ ├─ SearchIntents.php
│ ├─ SearchIndexes.php
│ ├─ SearchSources.php
│ └─ MeiliIntentRouter.php
├─ Contracts/
│ └─ SearchSourceContract.php
├─ Index/Autocomplete/
│ ├─ AutocompleteIndex.php
│ ├─ IndexConfigurator.php
│ ├─ AutocompleteIndexRegistry.php
│ └─ IndexSettings/
│ ├─ BrandSettings.php
│ ├─ CategorySettings.php
│ ├─ ProductSettings.php
│ ├─ SellerSettings.php
│ └─ ShopSettings.php
├─ Transformers/
│ ├─ BrandTransformer.php
│ ├─ CategoryTransformer.php
│ ├─ ProductTransformer.php
│ ├─ SellerTransformer.php
│ └─ ShopTransformer.php
├─ Sync/Pipelines/Autocomplete/...
├─ Sync/Dispatchers/Autocomplete/...
├─ Sync/MeiliSyncService.php
├─ Helpers/...
├─ Sources/
│ ├─ BrandSource.php
│ ├─ ProductSource.php
│ ├─ CategorySource.php
│ ├─ SellerSource.php
│ └─ ShopSource.php
```

---

## Engineering Diary (Design Discussions)

**GitHub Discussions:**  
https://github.com/GRIMaxx/myshop/discussions

Example topics:
- *Why autocomplete is not search*
- *Why per-index limits matter*
- *Why fallback UX is a product decision*

---

## Donate / Support

If this project is useful, you can support its development.  
⚠️ **Send only USDT on TRON (TRC20)**  

**Address / Адрес:**
```text
TANsgMvLpvVcn7AgspxzDPd2UkebdceeMM
```
<img src="public/assets/img/QR-Code.jpg" width="200" alt="QR code for donations">

---

Personal Note

- I don’t just write code
- I design systems to:

    - avoid unnecessary work
    - keep data consistent
    - behave predictably under load
    - This repo is a technical statement, not a marketing demo.

---

🇷🇺 Кратко по-русски

Полностью собрана система автоподсказок
Система поиска в разработке, будет продолжение
Синхронизация MySQL → Meili DB полностью отработана
Показано, как строятся документы, transformers, pipelines и очередь
Подробное объяснение логики, бизнес-проверок и зависимостей
Все демонстрации видео и посты поэтапно

---

## Architecture Diagram

🗄️ MySQL / PhpMyAdmin
│
▼
📦 Domain Layer
├─ Models
├─ Observers
├─ Dispatcher
└─ Pipeline
│
▼
🟦 Redis
├─ Streams
├─ Queues
├─ Cache
└─ Sessions
│
▼
👥 Consumer Groups
└─ ConsumeUpdates
│
▼
🔒 Locking (SETNX / 5s)
│
▼
🧠 Index Brain
├─ MeiliIntentRouter
└─ Dependency Graph
│
▼
⚙️ Transformers & Document Builders
│
▼
📤 Queue Layer (Horizon Jobs)
│
▼
📚 MeiliSearch Indexes
└─ autocomplete_*
│
▼
🌐 API / Frontend
└─ Autocomplete Results

























































