# ServiceNow Archive

ServiceNow Archive is a Laravel and Inertia application for keeping a local, read-only archive of legacy ServiceNow task records. It connects to a ServiceNow instance, syncs selected task-derived tables, stores records, journal entries, and attachments locally, and provides a simple browser UI for searching and reviewing archived data.

## What It Does

- Stores a ServiceNow connection with instance URL, username, and encrypted password.
- Tests the configured ServiceNow connection.
- Discovers task-derived ServiceNow tables when the account has access.
- Allows manual table entry when table discovery is blocked by ServiceNow ACLs.
- Syncs configured tables into local database tables.
- Archives record fields, comments, work notes, and attachment metadata/files.
- Provides pages for records, table configuration, sync runs, and connection settings.

## Main Pages

- `/servicenow/records` - browse and filter archived records.
- `/servicenow/tables` - add tables, set fetch dates, and trigger syncs.
- `/servicenow/runs` - view sync history.
- `/servicenow/connection` - configure and test ServiceNow credentials.

The root path redirects to the records archive. The old dashboard page has been removed.

## ServiceNow Setup

Create an account in the app, then open `/servicenow/connection` and enter:

- ServiceNow instance URL, such as `https://example.service-now.com`
- Service account username
- Service account password

If the connection test succeeds but no tables are discovered, enter table names manually on `/servicenow/tables`. Common task-derived tables include `incident`, `sc_task`, `change_request`, and `problem`.

## Local Development

Install dependencies and build assets:

```bash
composer install
npm install
npm run build
php artisan migrate
```

The app is designed to run under Laravel Herd in this workspace. Use the configured Herd URL, then register or log in to access the archive pages.

## Verification

Useful checks:

```bash
php artisan test --compact
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --format agent
npm run lint
npm run build
```

