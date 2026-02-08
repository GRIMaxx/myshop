# Production-grade Search & Autocomplete Engine

Laravel · Redis Streams · MeiliSearch · Event-Driven Architecture

------------------------------------------------------------------------

## This is NOT a search demo

This repository documents how a **real-world search and autocomplete
system** is **designed, evolved, and stress-tested under production
constraints**.

The focus here is:

-   architecture
-   data flow
-   trade-offs
-   failure modes
-   system evolution

Not frameworks.\
Not quick integrations.\
Not toy examples.

------------------------------------------------------------------------

## Demo (real frontend, real data)

This short video shows how autocomplete behaves in practice:

-   instant feedback
-   multiple entity types
-   predictable latency
-   stable behavior under rapid queries

Watch demo (frontend autocomplete in action):\
https://youtu.be/t_PeFxUbrv4

------------------------------------------------------------------------

Laravel 12 project file structure demo:\
https://youtu.be/WlKlPhlQNAU

------------------------------------------------------------------------

Infrastructure overview:

-   Redis Insight: https://youtu.be/DseAV2AoFbM
-   Horizon / Meilisearch / PhpMyAdmin: https://youtu.be/aBYBvXBVono

------------------------------------------------------------------------

*(The full backend implementation lives in private branches.\
This repository focuses on architecture and engineering approach.)*

------------------------------------------------------------------------

## Problems this project addresses

-   Preventing **reindex storms** under burst updates
-   Maintaining search consistency without full reindex
-   Scaling autocomplete across multiple entity domains
-   Avoiding over-fetching and unnecessary ranking work
-   Ensuring predictable latency under load
-   Supporting deep relational indexing (multi-table dependencies)

------------------------------------------------------------------------

## Project Goal

This project demonstrates how **search infrastructure should be
designed** --- not just how to "connect MeiliSearch to Laravel".

It reflects real production thinking:

-   correctness first
-   explicit data flow
-   debuggable behavior
-   observable pipelines
-   controlled failure recovery
-   predictable scaling strategy

------------------------------------------------------------------------

## High-Level Architecture

``` php
MySQL
↓
Models
↓
Observers (emit domain event)
↓
IndexIntentBuilder
↓
RedisStreamPublisher
↓
Redis Streams
↓
StreamConsumer
↓
IndexDependencyGraph
↓
IntentRouter
↓
Queue Jobs (Isolated Indexing Workers)
↓
MeiliSearch
↓
Search / Autocomplete API
↓
Frontend (React)
```

------------------------------------------------------------------------

## 🔑 Core Concepts

### Event-Driven Synchronization

-   Database changes emit explicit domain events
-   No polling
-   No full reindex cycles
-   Every index update has a traceable cause

------------------------------------------------------------------------

### Redis Streams + Consumer Groups

-   Persistent event log
-   Reliable delivery guarantees
-   Horizontal scaling of workers
-   ACK only after successful processing
-   Safe recovery of pending messages

------------------------------------------------------------------------

### Idempotency & Locking

-   Redis-based locks prevent duplicate rebuilds
-   Burst updates collapse into a single indexing operation
-   One logical change → one index rebuild

------------------------------------------------------------------------

### Queue Isolation

-   Business logic separated from indexing
-   Dedicated queue for search indexing
-   Throughput controlled via Horizon workers

------------------------------------------------------------------------

### Declarative Relation Graph

-   Search impact rules defined declaratively
-   Supports deep relational graphs (4--6+ joins)
-   No hidden Eloquent chains
-   Query behavior is transparent and auditable

------------------------------------------------------------------------

### Search Index Design

-   Dedicated transformers per entity type
-   Multilingual-ready document structure
-   Alias and normalization handling
-   Separate autocomplete indexes (not reused search indexes)
-   Controlled document rebuild logic

------------------------------------------------------------------------

## Production Mindset

-   Supervisor + Horizon process management
-   Redis separation (cache / queues / streams)
-   Memory and process control
-   Structured pipeline logging
-   Traceable indexing flows
-   Designed to scale with both data and traffic

------------------------------------------------------------------------

## Engineering Diary (Design Discussions)

Design decisions and architectural reasoning:

GitHub Discussions:\
https://github.com/GRIMaxx/myshop/discussions

Example topics:

-   Why autocomplete is not search
-   Why per-index limits matter
-   Why fallback UX is a product decision
-   Trade-offs between indexing granularity and performance

This format reflects how real systems evolve.

------------------------------------------------------------------------

## Why the full code is not public

This repository is an architecture showcase.

The complete implementation lives in a private branch and is available:

-   for technical interviews
-   for employer review

This protects the work while still demonstrating real engineering
expertise.

------------------------------------------------------------------------

## Who this project is for

-   Marketplaces
-   E-commerce platforms
-   Catalog-heavy systems
-   Products where search is core infrastructure

------------------------------------------------------------------------

## Personal Note

I don't just write code.

I design systems that:

-   avoid unnecessary work
-   maintain data consistency
-   remain observable and debuggable
-   behave predictably under load

This repository is a technical statement --- not a marketing demo.

------------------------------------------------------------------------

## 🇷🇺 Кратко по-русски

Этот репозиторий --- не демо и не open-source библиотека.

Он показывает:

-   как проектируется production-grade поиск
-   как обеспечивается консистентность данных
-   как масштабируется autocomplete
-   какие архитектурные решения принимаются и почему

Полный код находится в приватной ветке и доступен для собеседований.

------------------------------------------------------------------------

-   📧 Email: servicegxx@gmail.com
-   💼 LinkedIn: https://www.linkedin.com/in/roman-hevorkian-b9b5b6383

------------------------------------------------------------------------

# Support the Project / Поддержка проекта

This project documents the development of a search system from scratch.

If you find the architecture or ideas useful, you can support its
development.

------------------------------------------------------------------------

## What is already done

-   Autocomplete & suggestion engine
-   Event-driven indexing pipeline
-   Multi-index dependency architecture

------------------------------------------------------------------------

## What's next

-   Full search ranking engine
-   Advanced query logic
-   Performance tuning and load testing
-   Observability tooling

------------------------------------------------------------------------

## Donate

If this project helped you learn or inspired your architecture
decisions, you can support further development.

⚠️ Important:

Send only USDT on the TRON network (TRC20).\
Any other tokens or networks will be permanently lost.

------------------------------------------------------------------------

### USDT (TRC20 / TRON)

``` php
TANsgMvLpvVcn7AgspxzDPd2UkebdceeMM
```

`<img src="public/assets/img/QR-Code.jpg" width="200" alt="Support the project via USDT TRC20">`{=html}
