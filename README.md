# Production-grade Search & Autocomplete Engine

Laravel · Redis Streams · MeiliSearch · Event-Driven Architecture

---

## This is NOT a search demo

This repository documents how a **real-world search and autocomplete system**
is **designed, evolved, and stress-tested under production constraints**.

The focus here is:
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

*(The full backend implementation is private. This repository focuses on architecture.)*

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
not just how to “connect MeiliSearch to Laravel”.

It reflects real production thinking:
- correctness first
- explicit data flow
- debuggable behavior
- controlled failure recovery

---

## High-Level Architecture

MySQL (Domain Data)
↓
Domain Events (Observers)
↓
Redis Streams (Event Log)
↓
Idempotent Stream Consumers
↓
Isolated Queue Jobs (Indexing)
↓
MeiliSearch Indexes
↓
Search / Autocomplete API
↓
Frontend (React)


---

## 🔑 Core Concepts

### Event-Driven Synchronization
- Database changes emit **explicit domain events**
- No polling
- No full reindex
- Every index update has a traceable cause

### Redis Streams + Consumer Groups
- Persistent event log
- Reliable delivery
- Horizontal scalability
- ACK only after successful processing
- Safe recovery of pending messages

### Idempotency & Locking
- Redis-based locks prevent duplicate rebuilds
- Burst updates collapse into a single indexing operation
- One logical change → one index rebuild

### Queue Isolation
- Business logic and indexing are fully separated
- Heavy indexing jobs run in a dedicated queue
- Throughput controlled via Horizon

### Declarative Relation Graph
- Search impact rules are described explicitly
- Supports deep relational graphs (4–6+ joins)
- No hidden Eloquent relationship chains
- Query behavior is transparent and auditable

### Search Index Design
- Dedicated transformers per document type
- Multilingual-ready
- Alias and normalization handling
- Separate autocomplete indexes (not reused search indexes)

---

## Production Mindset

- Supervisor + Horizon
- Redis separation (cache / queues / streams)
- Memory and process control
- Full pipeline logging
- Designed to scale with both data and traffic

---

## Engineering Diary (Design Discussions)

Design decisions and architectural reasoning are documented here:

**GitHub Discussions:**
https://github.com/GRIMaxx/myshop/discussions

Example topics:
- *Why autocomplete is not search*
- *Why per-index limits matter*
- *Why fallback UX is a product decision*

This format is intentional — it reflects how real systems evolve.

---

## Why the full code is not public

This repository is an **architecture showcase**.

The complete implementation lives in a **private branch** and is available:
- for technical interviews
- for employer review

This protects the work while still demonstrating real engineering expertise.

---

## Who this project is for

- Marketplaces
- E-commerce platforms
- Catalog-heavy systems
- Products where search is **core infrastructure**

---

## Personal Note

I don’t just write code.

I design systems that:
- avoid unnecessary work
- keep data consistent
- behave predictably under load

This repository is a **technical statement**, not a marketing demo.

---

## 🇷🇺 Кратко по-русски

Этот репозиторий - не демо и не open-source библиотека.

Он показывает:
- как проектируется production-grade поиск
- как решаются проблемы консистентности данных
- как масштабируется autocomplete
- какие архитектурные решения принимаются и почему

Полный код находится в приватной ветке и доступен для собеседований.

---

- 📧 Email: servicegxx@gmail.com
- 💼 LinkedIn: https://www.linkedin.com/in/roman-hevorkian-b9b5b6383

---

# Support the Project / Поддержка проекта

I’m building a **search engine from scratch** and documenting the entire process step by step.

Я создаю **поисковую систему с нуля** и подробно показываю весь процесс разработки.

---

## What is already done / Что уже сделано

- Autocomplete & suggestions system  
- Система автоподсказок

## What’s next / Что дальше

- Full search engine: indexing, ranking, queries  
- Полноценный поиск: индексация, ранжирование, обработка запросов

All code, architecture decisions, and mistakes are published openly.  
Весь код, архитектура, решения и ошибки публикуются открыто.

---

## Donate / Донаты

If this project is useful to you, you can support its development.  
Если проект оказался полезным — можно поддержать его донатом.

⚠️ **Important / Важно**  
Send **only USDT on the TRON network (TRC20)**.  
Отправляйте **только USDT в сети TRON (TRC20)**.  

Any other tokens or networks **will be permanently lost**.  
Любые другие токены или сети **будут безвозвратно утеряны**.

---

### USDT (TRC20 / TRON)

**Address / Адрес:**  
```php 
TANsgMvLpvVcn7AgspxzDPd2UkebdceeMM
```

<img src="public/assets/img/QR-code.jpg" width="200" alt="QR code for donations">
