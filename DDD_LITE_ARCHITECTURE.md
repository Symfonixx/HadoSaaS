# DDD-lite Conventions For HadoSaaS

This file defines the project-wide conventions for incremental DDD-lite refactoring.

## Layer Responsibilities

- `Http/Controllers`: transport layer only (request parsing, response/view rendering).
- `Application/*`: use-case orchestration and cross-cutting business workflow.
- `Application/*/Commands` and `Application/*/Queries`: explicit input DTOs for use-cases.
- `Repositories/*`: persistence/query operations only (Eloquent access, no HTTP coupling).
- `Models/*`: entity state and relationships, avoid orchestration logic.

## Module Folder Shape

Each module can adopt the following structure:

- `app/Application/<Context>/Commands`
- `app/Application/<Context>/Queries`
- `app/Application/<Context>/<UseCase>Service.php`
- `app/Repositories/<Entity>/<Entity>Repository.php`

## Dependency Rules

- Controllers depend on application services.
- Application services depend on repository interfaces and cross-cutting contracts.
- Repositories depend on models and framework persistence APIs.
- Models should not depend on request/session state.

## Cross-cutting Contracts

Shared concerns are injected through contracts and implemented in infrastructure code:

- translation
- flash messaging
- cache invalidation policy

## Query Boundary Rules

- Repositories do not call `request()` directly.
- Query filtering is passed in from controllers/services via explicit query DTOs.

## Migration Strategy

- Keep route names, view contracts, and response shapes stable during phase-1.
- Refactor one vertical slice at a time (list/create/update/delete per entity).
- Keep backward compatibility at interfaces where possible, then tighten contracts after stabilization.
