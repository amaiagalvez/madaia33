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
    USERS --> USER_LOGIN_SESSIONS
    USERS --> OWNER_AUDIT_LOGS
    USERS --> ROLE_USER
    ROLES --> ROLE_USER
    USERS --> LOCATION_USER
    LOCATIONS --> LOCATION_USER
    OWNERS --> PROPERTY_ASSIGNMENTS
    OWNERS --> OWNER_AUDIT_LOGS
    LOCATIONS --> PROPERTIES
    PROPERTIES --> PROPERTY_ASSIGNMENTS
    NOTICES --> NOTICE_LOCATIONS
    LOCATIONS --> NOTICE_LOCATIONS
    VOTINGS --> VOTING_OPTIONS
    VOTINGS --> VOTING_LOCATIONS
    LOCATIONS --> VOTING_LOCATIONS
    VOTINGS --> VOTING_BALLOTS
    OWNERS --> VOTING_BALLOTS
    USERS --> VOTING_BALLOTS
    VOTING_BALLOTS --> VOTING_SELECTIONS
    VOTING_OPTIONS --> VOTING_SELECTIONS
    VOTINGS --> VOTING_OPTION_TOTALS
    VOTING_OPTIONS --> VOTING_OPTION_TOTALS
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

    ROLES {
        bigint id
        string name
        datetime deleted_at
    }

    ROLE_USER {
        bigint role_id
        bigint user_id
    }

    SESSIONS {
        string id
        bigint user_id
        integer last_activity
    }

    USER_LOGIN_SESSIONS {
        bigint id
        bigint user_id
        bigint impersonator_user_id
        string session_id
        string ip_address
        timestamp logged_in_at
        timestamp logged_out_at
        datetime deleted_at
    }

    LOCATIONS {
        bigint id
        string code
        string name
        datetime deleted_at
    }

    LOCATION_USER {
        bigint location_id
        bigint user_id
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
        timestamp accepted_terms_at
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

    VOTINGS {
        bigint id
        string name_eu
        string name_es
        text question_eu
        text question_es
        date starts_at
        date ends_at
        boolean is_published
        boolean is_anonymous
        datetime deleted_at
    }

    VOTING_OPTIONS {
        bigint id
        bigint voting_id
        string label_eu
        string label_es
        smallint position
        datetime deleted_at
    }

    VOTING_LOCATIONS {
        bigint id
        bigint voting_id
        bigint location_id
        datetime deleted_at
    }

    VOTING_BALLOTS {
        bigint id
        bigint voting_id
        bigint owner_id
        bigint cast_by_user_id
        string cast_ip_address
        decimal cast_latitude
        decimal cast_longitude
        timestamp voted_at
        datetime deleted_at
    }

    VOTING_SELECTIONS {
        bigint id
        bigint voting_id
        bigint voting_ballot_id
        bigint owner_id
        bigint voting_option_id
        datetime deleted_at
    }

    VOTING_OPTION_TOTALS {
        bigint id
        bigint voting_id
        bigint voting_option_id
        integer votes_count
        datetime deleted_at
    }

    USERS ||--o{ OWNERS : has_many
    USERS ||--o{ SESSIONS : has_many
    USERS ||--o{ OWNER_AUDIT_LOGS : changed_by
    USERS ||--o{ USER_LOGIN_SESSIONS : login_events
    USERS ||--o{ ROLE_USER : has_many
    ROLES ||--o{ ROLE_USER : has_many
    USERS ||--o{ LOCATION_USER : manages
    OWNERS ||--o{ PROPERTY_ASSIGNMENTS : has_many
    OWNERS ||--o{ OWNER_AUDIT_LOGS : has_many
    OWNERS ||--o{ VOTING_BALLOTS : has_many
    LOCATIONS ||--o{ PROPERTIES : has_many
    LOCATIONS ||--o{ LOCATION_USER : has_many
    PROPERTIES ||--o{ PROPERTY_ASSIGNMENTS : has_many
    USERS ||--o{ VOTING_BALLOTS : delegated_by
    VOTINGS ||--o{ VOTING_OPTIONS : has_many
    VOTINGS ||--o{ VOTING_LOCATIONS : has_many
    VOTINGS ||--o{ VOTING_BALLOTS : has_many
    VOTINGS ||--o{ VOTING_SELECTIONS : has_many
    VOTINGS ||--o{ VOTING_OPTION_TOTALS : has_many
    LOCATIONS ||--o{ VOTING_LOCATIONS : has_many
    VOTING_OPTIONS ||--o{ VOTING_SELECTIONS : has_many
    VOTING_OPTIONS ||--o{ VOTING_OPTION_TOTALS : has_many
    VOTING_BALLOTS ||--o{ VOTING_SELECTIONS : has_many
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
        bigint location_id
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
    LOCATIONS ||--o{ NOTICE_LOCATIONS : has_many
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
