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
    AUTH["Auth & access\nusers, roles, sessions"]:::auth
    OWNERSHIP["Ownership\nowners, locations, properties, assignments"]:::ownership
    VOTINGS_G["Votings\nvotings, ballots, selections, totals"]:::votings
    CAMPAIGNS_G["Campaigns\ncampaigns, recipients, documents, templates"]:::campaigns
    CONTENT["Content & settings\nnotices, images, contact messages, settings"]:::content
    TRACKING["Tracking\nnotice_reads, notice_document_downloads"]:::content

    AUTH -->|identity and permissions| OWNERSHIP
    AUTH -->|acting users| VOTINGS_G
    AUTH -->|authors and senders| CAMPAIGNS_G

    OWNERSHIP -->|owners and locations| VOTINGS_G
    OWNERSHIP -->|owners and locations| CAMPAIGNS_G
    OWNERSHIP -->|location-scoped notices| CONTENT
    OWNERSHIP -->|owner read state| TRACKING
    CONTENT -->|notice audit trail| TRACKING

    classDef auth      fill:#dbeafe,stroke:#3b82f6,color:#1e3a5f
    classDef ownership fill:#dcfce7,stroke:#22c55e,color:#14532d
    classDef votings   fill:#fef9c3,stroke:#eab308,color:#713f12
    classDef campaigns fill:#ffe4e6,stroke:#f43f5e,color:#881337
    classDef content   fill:#f3e8ff,stroke:#a855f7,color:#581c87
```

### 2a) Core domain — ownership (owners, locations, properties)

```mermaid
flowchart LR
    A["Auth & access + Ownership domain"]:::ownership
    classDef ownership fill:#dcfce7,stroke:#22c55e,color:#14532d
```

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

    USERS ||--o{ OWNERS : has_many
    USERS ||--o{ SESSIONS : has_many
    USERS ||--o{ OWNER_AUDIT_LOGS : changed_by
    USERS ||--o{ USER_LOGIN_SESSIONS : login_events
    USERS ||--o{ ROLE_USER : has_many
    ROLES ||--o{ ROLE_USER : has_many
    USERS ||--o{ LOCATION_USER : manages
    OWNERS ||--o{ PROPERTY_ASSIGNMENTS : has_many
    OWNERS ||--o{ OWNER_AUDIT_LOGS : has_many
    LOCATIONS ||--o{ PROPERTIES : has_many
    LOCATIONS ||--o{ LOCATION_USER : has_many
    PROPERTIES ||--o{ PROPERTY_ASSIGNMENTS : has_many
```

### 2b) Core domain — votings

```mermaid
flowchart LR
    A["Votings domain"]:::votings
    classDef votings fill:#fef9c3,stroke:#eab308,color:#713f12
```

```mermaid
erDiagram
    OWNERS {
        bigint id
        bigint user_id
        string coprop1_name
        datetime deleted_at
    }

    USERS {
        bigint id
        string name
        datetime deleted_at
    }

    LOCATIONS {
        bigint id
        string code
        string name
        datetime deleted_at
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
        decimal pct_total
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

    VOTINGS ||--o{ VOTING_OPTIONS : has_many
    VOTINGS ||--o{ VOTING_LOCATIONS : has_many
    VOTINGS ||--o{ VOTING_BALLOTS : has_many
    VOTINGS ||--o{ VOTING_SELECTIONS : has_many
    VOTINGS ||--o{ VOTING_OPTION_TOTALS : has_many
    LOCATIONS ||--o{ VOTING_LOCATIONS : has_many
    OWNERS ||--o{ VOTING_BALLOTS : has_many
    USERS ||--o{ VOTING_BALLOTS : delegated_by
    VOTING_OPTIONS ||--o{ VOTING_SELECTIONS : has_many
    VOTING_OPTIONS ||--o{ VOTING_OPTION_TOTALS : has_many
    VOTING_BALLOTS ||--o{ VOTING_SELECTIONS : has_many
```

### 2c) Core domain — campaigns

```mermaid
flowchart LR
    A["Campaigns domain"]:::campaigns
    classDef campaigns fill:#ffe4e6,stroke:#f43f5e,color:#881337
```

```mermaid
erDiagram
    USERS {
        bigint id
        string name
        datetime deleted_at
    }

    OWNERS {
        bigint id
        bigint user_id
        string coprop1_name
        datetime deleted_at
    }

    LOCATIONS {
        bigint id
        string code
        string name
        datetime deleted_at
    }

    CAMPAIGNS {
        bigint id
        bigint created_by_user_id
        string subject_eu
        string subject_es
        text body_eu
        text body_es
        string channel
        string status
        timestamp scheduled_at
        timestamp sent_at
        datetime deleted_at
    }

    CAMPAIGN_LOCATIONS {
        bigint id
        bigint campaign_id
        bigint location_id
        datetime deleted_at
    }

    CAMPAIGN_RECIPIENTS {
        bigint id
        bigint campaign_id
        bigint owner_id
        string slot
        string contact
        string tracking_token
        string status
        string message_subject
        text message_body
        timestamp sent_at
        bigint sent_by_user_id
        text error_message
        datetime deleted_at
    }

    CAMPAIGN_DOCUMENTS {
        bigint id
        bigint campaign_id
        string filename
        string path
        string mime_type
        bigint size_bytes
        boolean is_public
        datetime deleted_at
    }

    CAMPAIGN_TRACKING_EVENTS {
        bigint id
        bigint campaign_recipient_id
        bigint campaign_document_id
        string event_type
        text url
        string ip_address
    }

    CAMPAIGN_TEMPLATES {
        bigint id
        bigint created_by_user_id
        bigint location_id
        string name
        string subject_eu
        string subject_es
        longtext body_eu
        longtext body_es
        string channel
        datetime deleted_at
    }

    USERS ||--o{ CAMPAIGNS : created_by
    CAMPAIGNS ||--o{ CAMPAIGN_LOCATIONS : has_many
    LOCATIONS ||--o{ CAMPAIGN_LOCATIONS : has_many
    CAMPAIGNS ||--o{ CAMPAIGN_RECIPIENTS : has_many
    OWNERS ||--o{ CAMPAIGN_RECIPIENTS : has_many
    USERS ||--o{ CAMPAIGN_RECIPIENTS : sent_by
    CAMPAIGNS ||--o{ CAMPAIGN_DOCUMENTS : has_many
    CAMPAIGN_RECIPIENTS ||--o{ CAMPAIGN_TRACKING_EVENTS : has_many
    CAMPAIGN_DOCUMENTS ||--o{ CAMPAIGN_TRACKING_EVENTS : has_many
    USERS ||--o{ CAMPAIGN_TEMPLATES : created_by
    LOCATIONS ||--o{ CAMPAIGN_TEMPLATES : has_many
```

### 3) Content and settings domain

```mermaid
flowchart LR
    A["Content & settings domain"]:::content
    classDef content fill:#f3e8ff,stroke:#a855f7,color:#581c87
```

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

    NOTICE_READS {
        bigint id
        bigint notice_id
        bigint owner_id
        bigint user_id
        string ip_address
        timestamp opened_at
        datetime deleted_at
    }

    OWNERS {
        bigint id
        bigint user_id
        string coprop1_name
        datetime deleted_at
    }

    USERS {
        bigint id
        string name
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
    NOTICES ||--o{ NOTICE_READS : tracks
    LOCATIONS ||--o{ NOTICE_LOCATIONS : has_many
    OWNERS ||--o{ NOTICE_READS : opens
    USERS ||--o{ NOTICE_READS : records
```

### 4) Framework tables

```mermaid
flowchart LR
    A["Framework tables"]:::framework
    classDef framework fill:#f1f5f9,stroke:#94a3b8,color:#334155
```

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
