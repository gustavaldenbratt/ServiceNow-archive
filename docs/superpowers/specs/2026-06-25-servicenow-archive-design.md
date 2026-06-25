# ServiceNow Task Archive — Design

**Date:** 2026-06-25
**Status:** Approved (initial scope)

## Purpose

We are decommissioning ServiceNow and do not want to migrate every table. Instead we
need a web app that fetches and faithfully stores complete data from ServiceNow **task**
tables — including journal entries (comments / work notes) and attachments (images and
other files) — so the records remain available for **read-only lookup and reference**
after ServiceNow is shut off.

Users define, in the UI, **which tables** to archive and **how far back** to fetch. The
sync is **re-runnable / incremental** so it can be run repeatedly until cut-over.

### Goal type

Read-only archive. The app is for searching and viewing historical records. It is **not**
a working ITSM going forward and is **not** a transform/load staging area for another
system.

## Scope

### In scope

- Connect to a single ServiceNow instance via the REST Table/Attachment APIs using basic
  auth (service-account username/password). Instance is reachable directly over HTTPS.
- Discover all tables that **extend the `task` table** and let the user choose which to
  archive.
- Dynamic schema: any task-extending table (including custom `u_*` tables) can be archived
  with **zero code/schema changes**.
- Fetch and store, per record: the complete field set, all journal entries, and all
  attachments (files on disk).
- Incremental, re-runnable sync with a configurable "fetch since" floor per table.
- UI for: connection settings, table configuration, sync runs/progress, record browsing
  with simple filtering, and a faithful record detail view.
- All behind the existing Fortify authentication.

### Out of scope (for now)

- Creating or editing ServiceNow records (read-only archive).
- Full-text search across descriptions/journals (simple filtering only for v1; can be
  added later).
- Per-column SQL filtering on arbitrary dynamic fields.
- OAuth / MID-server / VPN-only connectivity.
- Multiple simultaneous ServiceNow instances.

## Storage approach

**Generic JSON store.** A fixed, small set of tables regardless of how many ServiceNow
tables are archived. Adding a new task table requires no schema migration. Filtering is
served by a few "promoted" columns plus the raw JSON for faithful viewing. Chosen over
per-table dynamic migrations (too complex for read-only viewing) and a document database
(unnecessary new infrastructure on the Laravel/MySQL stack).

## Data model

### `sn_records`
One row per ServiceNow record.
- `table_name`, `sys_id` — unique together
- `number`
- Promoted filter columns: `sn_created_at`, `sn_updated_at`, `state`,
  `short_description`, `assigned_to`
- `data` (JSON) — complete field set, stored with both raw values and display values
  (`sysparm_display_value=all`) so the detail view renders faithfully
- timestamps

### `sn_journal_entries`
- `sys_id` — unique
- `record_sys_id`, `table_name`
- `element` — `comments` or `work_notes`
- `value`
- `created_by`, `created_at`

### `sn_attachments`
- `sys_id` — unique
- `record_sys_id`, `table_name`
- `file_name`, `content_type`, `size`
- `storage_path` — file bytes stored on the Laravel filesystem
  (e.g. `sn-attachments/{table}/{record_sys_id}/{attachment_sys_id}`)

### `sn_table_configs`
- `table_name`
- `fetch_since` — date floor for the first pull
- `enabled`
- `last_synced_cursor` — max `sys_updated_on` processed
- `last_run_status`

### `sync_runs`
- `table_name`, `status`, `records_processed`, `total`, `started_at`, `finished_at`,
  `error`

### `sn_connection` (single row, settings)
- `instance_url`, `username`, `password` — credentials encrypted at rest

## Components & boundaries

- **`ServiceNowClient`** — thin HTTP wrapper: basic auth, pagination, rate-limit +
  retry/backoff. Methods: `listTables()`, `fetchRecords(table, query, offset, limit)`,
  `fetchJournal(table, sysIds)`, `listAttachments(table, sysIds)`,
  `downloadAttachment(sysId)`.
- **`TaskTableResolver`** — reads `sys_db_object`, resolves the super-class chain, returns
  every table descending from `task`.
- **`RecordSyncService`** — orchestrates one table's sync: page records, upsert, pull
  journals & attachments, advance cursor.
- **`SyncTableJob`** (queued) — wraps the service per table per run, writes progress to
  `sync_runs`.
- **Models** — `SnRecord`, `SnJournalEntry`, `SnAttachment`, `SnTableConfig`, `SyncRun`,
  `SnConnection`.
- **Controllers + Inertia/React pages** — connection settings, tables, runs, records,
  record detail.

## Data flow (one sync run)

1. For each enabled table config, dispatch a `SyncTableJob`.
2. The job queries records where `sys_updated_on >= cursor` (first run uses `fetch_since`),
   ordered by `sys_updated_on`, paginated.
3. For each page: upsert records by `(table_name, sys_id)`; fetch & upsert journal entries
   (dedup by entry `sys_id`); list attachments and download any not already stored
   (dedup by attachment `sys_id`).
4. Advance the cursor only after a page fully succeeds, so re-runs resume cleanly and stay
   incremental. Records updated in ServiceNow are re-fetched and upserted on later runs.

## UI

- **Connection settings** — instance URL + service account (encrypted); "Test connection".
- **Tables** — configured tables with last-sync status; add one by picking from discovered
  task-extending tables; set "fetch since"; enable/disable; "Sync now".
- **Runs** — history and live progress of syncs.
- **Records** — list filtered by table / number / date range / state; paginated.
- **Record detail** — all fields as label/value; merged journal timeline (comments +
  work notes, chronological); attachment gallery with inline images.

## Error handling

- API errors retry with exponential backoff. A page that ultimately fails fails the run
  **without advancing the cursor**, so the next run resumes from the last good point.
- Per-attachment download failures are logged but do not fail the run.
- Unknown / changing ServiceNow fields cannot break ingestion — the full payload is stored
  verbatim as JSON.

## Testing (Pest)

Feature tests using `Http::fake()` to simulate the ServiceNow APIs:

- Task-table discovery from `sys_db_object` (super-class chain resolution).
- Record pagination and the incremental cursor (first run uses `fetch_since`; later runs
  use `last_synced_cursor`; cursor does not advance on failure).
- Journal and attachment fetch + dedup/upsert.
- UI controllers: connection settings, table config CRUD, run dispatch, record browse
  filtering, record detail rendering.

## Open items / assumptions

- **Volume** — sized for large pulls (tens of thousands+). Queued jobs with pagination and
  resume handle any size; revisit batch sizes / throttling once real volume is known.
- Single ServiceNow connection for v1.
- Additional needs can be added iteratively after this initial scope ships.
