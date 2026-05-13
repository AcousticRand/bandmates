# BandMates

A multi-tenant band management application built with Laravel 13, Filament 5, and Livewire 4. Each band operates as an isolated tenant — members manage their own song repertoire, setlists, venues, and gig schedule without seeing other bands' data.

---

## Features

- **Band management** — Create a band, invite members, and assign roles
- **Song library** — Track your repertoire with lyrics, performance notes, and runtime
- **CSV import** — Bulk-import songs from a spreadsheet
- **Setlists** — Build named setlists divided into 1–3 sets using a drag-and-drop board
- **Venues** — Maintain a book of venues your band plays
- **Gigs** — Schedule shows by linking a date, venue, and setlist

---

## Tech Stack

| Layer         | Package                | Version |
| ------------- | ---------------------- | ------- |
| Framework     | Laravel                | 13      |
| Admin panel   | Filament               | 5       |
| Reactive UI   | Livewire               | 4       |
| CSS           | Tailwind CSS           | 4       |
| Multi-tenancy | laraveldaily/filateams | —       |
| Kanban board  | relaticle/flowforge    | —       |
| Testing       | Pest                   | 4       |

---

## Local Development Setup

### Prerequisites

- PHP 8.5+
- Composer
- Node.js 20+ and npm
- MySQL 8+ (or SQLite for quick local runs)

### Installation

```bash
git clone <repo>
cd bandmates

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then:

```bash
php artisan migrate
npm run build
```

### Running Locally

```bash
composer run dev   # starts Laravel, queue worker, and Vite in parallel
```

The panel is available at `http://localhost:8000/admin`.

### Creating the First User

The best way is to go to the panel, and instead of logging in, you can "Sign up new user".  Then, after creating the
user, once logged in, go to the dropdown in the upper-left, under "Bandmates", and create new team.

Alternatively, you can run the following command to create a user:
```bash
php artisan make:filament-user
```

After logging in, create a band via the "New Band" button in the tenant switcher.

---

## Multi-Tenancy

All data is scoped to a **band (team)**. The URL structure is:

```
/admin/{band-slug}/songs
/admin/{band-slug}/setlists
/admin/{band-slug}/gigs
...
```

Filament's native tenancy (via FilaTeams) ensures that a user logged into Band A cannot read or modify Band B's records. A user can belong to multiple bands and switch between them via the panel header.

---

## Band Roles

Roles are defined in `app/Enums/BandRole.php`.

| Role          | Description                                       | Can manage band settings |
| ------------- | ------------------------------------------------- | ------------------------ |
| **Owner**     | Created the band; full permissions                | Yes                      |
| **Performer** | Default role for invited members                  | No                       |
| **Crew**      | Non-performing band member (sound, lights, etc.)  | No                       |
| **Other**     | Catch-all for associates                          | No                       |

Only the Owner can invite/remove members and change roles. Band ownership is not transferable through the UI.

---

## Data Model

```
Band (Team)
├── Songs
│   └── SongAnnotations (private per-member notes)
├── Setlists
│   └── SetlistItems  (songs arranged into sets)
├── Venues
└── Gigs  (links a Venue + Setlist + date)
```

All top-level models (`Song`, `Setlist`, `Venue`, `Gig`) have a `team_id` foreign key. Filament automatically scopes every query to the current band.

---

## Songs

**Navigation:** Repertoire → Songs

### Fields

| Field        | Notes                                                                     |
| ------------ | ------------------------------------------------------------------------- |
| Title        | Required                                                                  |
| Artist       | The original recording artist                                             |
| Album        | The specific album/record this arrangement is based on                    |
| Genre        | —                                                                         |
| Release Year | —                                                                         |
| Key          | Musical key (C, C#, D, …)                                                |
| Tempo        | BPM or description                                                        |
| Runtime      | Entered and displayed as `mm:ss`; stored in the database as total seconds |
| Arrangement  | Free-text performance notes                                               |
| Lyrics       | Full lyrics                                                               |
| Notes        | Internal band notes                                                       |

### Runtime Format

Runtime is a first-class field. Enter values as `m:ss` or `mm:ss` (e.g. `3:45` = 3 minutes 45 seconds). The database stores the integer second count; the UI always displays it as `mm:ss`.

### Annotations

Each member can attach private annotations to any song — visible only to themselves. Manage them via the **Annotations** tab on the song edit page.

### CSV Import

Use the **Import** button on the Songs list to bulk-upload from a CSV file. The importer supports column mapping, so headers don't need to match exactly. Required column: `title`. Optional: `artist`, `album`, `release_year`, `runtime`.

A notification is sent when the import completes. Failed rows can be downloaded as a separate CSV for review.

**Example CSV:**

```csv
title,artist,album,release_year,runtime
Bohemian Rhapsody,Queen,A Night at the Opera,1975,5:55
Hotel California,Eagles,Hotel California,1977,6:30
```

---

## Setlists

**Navigation:** Repertoire → Setlists

A setlist is a named collection of songs divided into 1, 2, or 3 sets. Create a setlist, set the number of sets, then click **Build Setlist** to arrange songs.

### Setlist Builder

The builder is a drag-and-drop kanban board:

- **Song Library** (leftmost column) — every song in your band's repertoire appears here
- **Set 1 / Set 2 / Set 3** — the number of set columns matches the setlist's "Number of Sets" setting

Drag songs from the library into a set to add them. Drag within a set to reorder. Drag between sets to move a song. The library column always shows songs that haven't been placed in any set yet.

Changes are saved automatically as you drag (powered by FlowForge's decimal-position ordering).

---

## Venues

**Navigation:** Bookings → Venues

A simple venue book scoped to your band.

| Field    | Notes                                     |
| -------- | ----------------------------------------- |
| Name     | Required                                  |
| Address  | Street address                            |
| City     | —                                         |
| State    | —                                         |
| Capacity | Optional integer                          |
| Notes    | Parking, load-in details, contacts, etc.  |

---

## Gigs

**Navigation:** Bookings → Gigs

Schedule a show by linking a date, venue, and setlist.

| Field   | Notes                                                           |
| ------- | --------------------------------------------------------------- |
| Name    | Short name for the show (e.g. "Friday Night at The Rusty Nail") |
| Date    | Show date                                                       |
| Venue   | Optional link to a saved venue                                  |
| Setlist | Optional link to a setlist                                      |
| Status  | `Upcoming` · `Completed` · `Cancelled`                         |
| Notes   | Free-text (load-in time, contact, etc.)                         |

---

## Running Tests

```bash
php artisan test --compact          # full suite
php artisan test --compact --filter=SongResourceTest   # single file
```

Tests use an in-memory SQLite database and Laravel's `RefreshDatabase` trait — no test database configuration is needed beyond what's in `phpunit.xml`.

### Test Coverage Areas

| File                   | What it covers                               |
| ---------------------- | -------------------------------------------- |
| `BandRoleTest`         | Role hierarchy, permissions, assignability   |
| `SongResourceTest`     | CRUD, runtime validation, album field        |
| `SongRuntimeTest`      | mm:ss ↔ seconds conversion (unit)            |
| `SongImportTest`       | CSV import, team scoping, validation         |
| `SetlistBuilderTest`   | Board page sync logic, tenant isolation      |
| `SetlistResourceTest`  | Setlist CRUD                                 |
| `VenueResourceTest`    | Venue CRUD                                   |
| `GigResourceTest`      | Gig CRUD, status field                       |
| `TenancyIsolationTest` | Cross-band access returns 404                |

---

## Project Structure

```
app/
├── Enums/
│   ├── BandRole.php         # Owner, Performer, Crew, Other
│   └── GigStatus.php        # Upcoming, Completed, Cancelled
├── Filament/
│   ├── Imports/
│   │   └── SongImporter.php
│   └── Resources/
│       ├── Gigs/
│       ├── Setlists/
│       │   └── Pages/
│       │       └── SetlistBoardPage.php   # FlowForge kanban board
│       ├── Songs/
│       └── Venues/
├── Livewire/                # (empty — board is handled by FlowForge)
├── Models/
│   ├── Gig.php
│   ├── Setlist.php
│   ├── SetlistItem.php      # position column for drag-drop ordering
│   ├── Song.php             # runtime stored as seconds
│   ├── SongAnnotation.php
│   └── Venue.php
└── Providers/
    ├── AppServiceProvider.php         # resolveRelationUsing for Team
    └── Filament/
        └── AdminPanelProvider.php     # panel config, plugins
resources/
└── css/
    └── filament/admin/theme.css       # custom Filament theme + card styles
```

---

## Key Implementation Notes

**Runtime storage** — `Song::runtime` is an Eloquent `Attribute` with a getter/setter. The setter parses `mm:ss` strings to integer seconds; the getter formats seconds back to `mm:ss`. The database column is an integer.

**Setlist builder sync** — When the Build Setlist page loads, it checks for any band songs that don't yet have a `SetlistItem` record for this setlist and creates them with `set_number = 0` (the library column). Songs already placed in sets (set_number ≥ 1) are left untouched.

**Tenant isolation in tests** — Filament's `BelongsToTenant` hook automatically forces `team_id` to the current Filament tenant on every model `creating` event. Tests that need to create records for a *different* team must use `DB::table()->insert()` to bypass this hook.

**FlowForge column ordering** — `setlist_items.position` is a `DECIMAL(20,10)` column. FlowForge uses fractional midpoint arithmetic to assign positions between existing cards, avoiding re-numbering on every drag.
