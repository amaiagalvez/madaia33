---
name: views-structure-mermaid
description: "Use this skill when you need to document, update, or review the structure of Blade views and their relationships (routes, layouts, components, includes, and Livewire mounts) as a Mermaid diagram in this Laravel project. Trigger on requests like: views diagram, Blade architecture, Mermaid views map, template relations, or route-to-view structure update."
license: MIT
metadata:
  author: madaia33
---

# Views Structure Mermaid

Use this skill to keep a single up-to-date Mermaid map of view architecture for this Laravel app.

## Source of truth

- Route entry points in `routes/web.php` and `routes/settings.php`.
- Blade templates in `resources/views/**`.
- Layout usage (`<x-layouts::...>`), includes (`@include(...)`), and Blade components (`<x-...>`).
- Livewire mounts in Blade (`<livewire:...>` and `Route::livewire(...)`).

## Update workflow

1. Read route files and list each route-to-view or route-to-livewire entry point.
2. Read affected Blade files and extract layout, include, component, and Livewire relations.
3. Update the Mermaid graph below.
4. Validate Mermaid syntax before finishing.

## Mermaid Views Map

```mermaid
flowchart LR
    WEB[web.php locale routes] --> PUB[public views]
    WEB --> ADM[admin views]
    SETTINGS[settings.php livewire routes] --> SETTINGSPAGES[settings livewire pages]
    PUB --> FRONTLAYOUT[layouts.front.main]
    ADM --> ADMINLAYOUT[layouts.admin.main]
    AUTHPAGES[auth pages] --> AUTHLAYOUT[layouts.shared.auth -> layouts.shared.auth.simple]
    DASHBOARDPAGE[pages.dashboard.index] --> APPLAYOUT[layouts.shared.app -> layouts.shared.app.sidebar]
```

### 1) Public routes, views and components

```mermaid
flowchart TD
    R1[web.php eu/es public routes] --> VPH[public.home]
    R1 --> VPN[public.notices]
    R1 --> VPG[public.gallery]
    R1 --> VPC[public.contact]
    R1 --> VPP[public.private]
    R1 --> VPL[public.legal-page]
    R1 --> VPV[public.votings]
    R1 --> VE404[errors.404]
    R1 --> VE500[errors.500]

    VPH --> LF[layouts.front.main]
    VPN --> LF
    VPG --> LF
    VPC --> LF
    VPP --> LF
    VPL --> LF
    VPV --> LF
    VE404 --> LF
    VE500 --> LF

    VPH --> LWH[livewire hero-slider]
    VPN --> LWN[livewire public-notices]
    VPG --> LWG[livewire image-gallery]
    VPC --> LWC[livewire contact-form]
    VPV --> LWV[livewire public-votings]
    LF --> LWL[livewire language-switcher]

    VPH --> CFNC[x-front.notice-card]
    LWN --> CFNC
    VPL --> CFPH[x-front.public-page-header]
    VPC --> CFPH
    VPG --> CFPH
    LWN --> CFPH
    LWG --> CFPH
    LWC --> CFPH
```

### 2) Admin routes, views and Livewire mounts

```mermaid
flowchart TD
    R2[web.php admin routes] --> VAD[admin.dashboard.index]
    R2 --> VAN[admin.notices]
    R2 --> VAI[admin.images]
    R2 --> VAM[admin.messages]
    R2 --> VAS[admin.settings]
    R2 --> VALI[admin.locations.index]
    R2 --> VALS[admin.locations.show]
    R2 --> VAOI[admin.owners.index]
    R2 --> VAOS[admin.owners.show]
    R2 --> VAV[admin.votings]

    VAD --> LA[layouts.admin.main]
    VAN --> LA
    VAI --> LA
    VAM --> LA
    VAS --> LA
    VALI --> LA
    VALS --> LA
    VAOI --> LA
    VAOS --> LA
    VAV --> LA

    VAD --> CADM[x-admin.page-header]
    VAN --> CADM
    VAI --> CADM
    VAM --> CADM
    VAS --> CADM
    VALI --> CADM
    VAV --> CADM

    VAN --> LMAN[livewire admin-notice-manager]
    VAI --> LMAI[livewire admin-image-manager]
    VAM --> LMAM[livewire admin-message-inbox]
    VAS --> LMAS[livewire admin-settings]
    VALI --> LMLO[livewire admin.locations]
    VALS --> LMLD[livewire admin.location-detail]
    VAOI --> LMOI[livewire admin.owners]
    VAOS --> LMOD[livewire admin.owner-detail]
    VAV --> LMOV[livewire admin.votings]
    LMAS --> CATB[admin settings tab partials]
```

### 3) Auth, dashboard and settings pages

```mermaid
flowchart TD
    AUTHVIEWS[pages.auth.* views] --> PAL[pages.auth.login]
    AUTHVIEWS --> PAF[pages.auth.forgot-password]
    AUTHVIEWS --> PAR[pages.auth.reset-password]
    AUTHVIEWS --> PAC[pages.auth.confirm-password]
    AUTHVIEWS --> PAT[pages.auth.two-factor-challenge]
    AUTHVIEWS --> PAV[pages.auth.verify-email]

    PAL --> LSH[layouts.shared.auth]
    PAF --> LSH
    PAR --> LSH
    PAC --> LSH
    PAT --> LSH
    PAV --> LSH
    LSH --> LASH[layouts.shared.auth.simple]

    PDB[pages.dashboard.index] --> LSA[layouts.shared.app]
    LSA --> LSS[layouts.shared.app.sidebar]

    R3[settings.php Route livewire] --> LSProfile[pages::settings.profile]
    R3 --> LSAppearance[pages::settings.appearance]
    R3 --> LSSecurity[pages::settings.security]

    PSP[pages.settings.profile blade] --> LSProfile
    PSA[pages.settings.appearance blade] --> LSAppearance
    PSS[pages.settings.security blade] --> LSSecurity

    PSP --> CSH[partials.admin.settings-heading]
    PSA --> CSH
    PSS --> CSH
```

### 4) Shared layout partials

```mermaid
flowchart LR
    LF[layouts.front.main] --> CPH[partials.shared.head]
    LASH[layouts.shared.auth.simple] --> CPH
    LSS[layouts.shared.app.sidebar] --> CPH
```

## Maintenance rule

If you add or modify any route that renders a view/livewire component, or change any Blade layout/include/component relation in `resources/views/**`, update this skill in the same task.
