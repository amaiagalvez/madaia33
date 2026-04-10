---
name: database-schema-mermaid
description: "Use this skill when you need to document, update, or review the database structure as a Mermaid ER diagram in this Laravel project. Trigger on requests like: database diagram, ERD, Mermaid DB schema, table relations, or migration structure update."
license: MIT
metadata:
  author: madaia33
---

# Database Schema Mermaid

Use this skill to keep a single up-to-date Mermaid ER diagram for the current Laravel database schema.

## Source of truth

- Migrations under `database/migrations/` are the source of truth.
- Reflect table creates, key columns, and foreign key relations.
- If a migration changes table structure or relations, this skill file must be updated in the same change.

## Update workflow

1. Read all migrations in `database/migrations/`.
2. List entities (tables) and key columns:
   - primary key
   - foreign keys
   - business-critical columns (status, dates, unique keys)
3. Extract relations from `foreignId()->constrained()` and explicit foreign declarations.
4. Update the ER diagram below.
5. Validate Mermaid syntax before finishing.

## Mermaid ERD

### 1) Relational overview

```mermaid
flowchart LR
    USERS --> OWNERS
    USERS --> SESSIONS
    USERS --> OWNER_AUDIT_LOGS
    OWNERS --> PROPERTY_ASSIGNMENTS
    OWNERS --> OWNER_AUDIT_LOGS
    LOCATIONS --> PROPERTIES
    PROPERTIES --> PROPERTY_ASSIGNMENTS
    NOTICES --> NOTICE_LOCATIONS
```

### 2) Core domain (community ownership)

```mermaid
erDiagram
    USERS {
        bigint id
        string name
        string email
        boolean is_active
        timestamp email_verified_at
        datetime deleted_at
    }

    SESSIONS {
        string id
        bigint user_id
        integer last_activity
    }

    LOCATIONS {
        bigint id
        string code
        string name
        datetime deleted_at
    }

    PROPERTIES {
        bigint id
        bigint location_id
        string name
        decimal community_pct
        decimal location_pct
        datetime deleted_at
    }

    OWNERS {
        bigint id
        bigint user_id
        string coprop1_name
        string coprop1_dni
        string coprop1_email
        datetime deleted_at
    }

    PROPERTY_ASSIGNMENTS {
        bigint id
        bigint property_id
        bigint owner_id
        date start_date
        date end_date
        boolean admin_validated
        boolean owner_validated
        datetime deleted_at
    }

    OWNER_AUDIT_LOGS {
        bigint id
        bigint owner_id
        bigint changed_by_user_id
        string field
        text old_value
        text new_value
    }

    USERS ||--o{ OWNERS : has_many
    USERS ||--o{ SESSIONS : has_many
    USERS ||--o{ OWNER_AUDIT_LOGS : changed_by
    OWNERS ||--o{ PROPERTY_ASSIGNMENTS : has_many
    OWNERS ||--o{ OWNER_AUDIT_LOGS : has_many
    LOCATIONS ||--o{ PROPERTIES : has_many
    PROPERTIES ||--o{ PROPERTY_ASSIGNMENTS : has_many
```

### 3) Content and settings domain

```mermaid
erDiagram
    NOTICES {
        bigint id
        string slug
        string title_eu
        string title_es
        boolean is_public
        datetime published_at
        datetime deleted_at
    }

    NOTICE_LOCATIONS {
        bigint id
        bigint notice_id
        string location_type
        string location_code
        datetime deleted_at
    }

    IMAGES {
        bigint id
        string filename
        string path
        string tag
        datetime deleted_at
    }

    CONTACT_MESSAGES {
        bigint id
        string name
        string email
        string subject
        boolean is_read
        datetime read_at
        datetime deleted_at
    }

    SETTINGS {
        bigint id
        string key
        text value
        string section
        datetime deleted_at
    }

    NOTICES ||--o{ NOTICE_LOCATIONS : has_many
```

### 4) Framework tables

```mermaid
erDiagram
    PASSWORD_RESET_TOKENS {
        string email
        string token
        timestamp created_at
    }

    CACHE {
        string key
        longtext value
        integer expiration
    }

    CACHE_LOCKS {
        string key
        string owner
        integer expiration
    }

    JOBS {
        bigint id
        string queue
        integer attempts
        integer available_at
    }

    JOB_BATCHES {
        string id
        string name
        integer total_jobs
        integer pending_jobs
    }

    FAILED_JOBS {
        bigint id
        string uuid
        string queue
        timestamp failed_at
    }
```

## Maintenance rule

If you add, remove, or modify any migration that changes tables, columns, unique keys, or foreign keys, update this skill in the same task.
