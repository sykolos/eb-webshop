# ebtest deployment

## Current release policy (D14)

The user chose to keep the already working deployment connection and server
configuration. A develop push deploys to https://ebtest.dekor26.hu after the
verify job succeeds. This includes documentation-only pushes. Feature branches
and pull requests run verification only. Production is not a deployment target.

There is no STAGING_DEPLOY_ENABLED switch, GitHub staging environment approval,
configuration-audit workflow, or requirement for new staging secrets.

Use the existing repository secrets:

| Setting | Purpose |
| --- | --- |
| SSS_HOST | Existing SSH host (the name is intentional) |
| SSH_USER | Existing deployment user |
| SSH_PORT | Existing SSH port |
| SSH_PASSWORD | Existing SSH password |
| DEPLOY_PATH | /home/dekorhu/ebtest/myapp |
| PUBLIC_PATH | /home/dekorhu/ebtest/public_html |
| ENV_DEVELOP | Existing optional first-install environment fallback |

The existing server .env is preserved. ENV_DEVELOP is used only when that file
does not exist. No environment, application key, mail recipient, cookie, queue
or database credentials are changed by this release. DEPLOY_PHP_BIN is optional;
the default is php, as in the previously successful deployment.

SSH uses the established password connection with StrictHostKeyChecking=no and
UserKnownHostsFile=/dev/null. This does NOT authenticate the server host key;
pinned host-key verification is deferred by D14, not marked as implemented.

The additional Basic-auth/mail-sink guards remain tested but inactive:
config/staging.php sets enabled=false. APP_ENV=staging alone does not activate
them. The existing application login and authorization are unchanged.
The .env.staging.example and check.php --staging describe a deferred stricter
configuration, not a current release prerequisite. Do not turn these guards
back on or require their secrets without a new user decision.

## Layout and retained checks

The document root remains /home/dekorhu/ebtest/public_html. Its generated
.laravel-path.php points to /home/dekorhu/ebtest/myapp, so HTTP and Artisan use
the same application. Local development keeps the ordinary public/ layout.

Verification installs dev dependencies from the lockfile, uses isolated SQLite
tests, builds frontend assets, and runs Bash/ShellCheck plus nine Linux
filesystem deployment scenarios. Release packaging separately installs no-dev
dependencies and reuses frontend assets from the same commit.

package.sh uses a runtime allowlist. ZIP references, local inspection files,
.env files, tests, documentation, session/cache data and uploads are not shipped.
Archives have SHA256 checksums and a REVISION matching the Git commit.

remote.sh retains checks that need no new credentials:
- App/public must be distinct siblings under /home/USER/ebtest.
- Refuse symlinked runtime paths and concurrent deployments.
- Validate archive checksums and REVISION before downtime.
- Boot a temporary candidate with a private copy of the actual existing .env.
- Validate the application key, public binding, routes, views and writable paths.
- Snapshot old code before replacement; preserve .env, uploads and .well-known.
- Recover a stale maintenance marker left by an interrupted deploy before
  running the new preflight.
- Refuse to delete a real public/storage directory; existing symlinks are supported.
- Use prerendered maintenance, run migrations and rebuild config/view caches.
- If a failure occurs after maintenance starts, clear the maintenance marker and
  bring the current release back online so a failed staging deploy does not
  leave the site at 503. The backup remains available for manual diagnosis and
  restoration.
- Emit phase markers during remote deployment so a failed SSH run identifies
  the last completed phase without exposing environment values.
- After the release is live, signal the existing supervised queue worker to
  restart when supported; a sync queue or unavailable cache must not turn a
  successful release into a maintenance-mode outage. This does not create a
  worker or change server settings.

No APP_ENV=staging, Basic credentials, email sink, new cookie settings or sync
queue are required by the current preflight. A missing/invalid APP_KEY still
fails; never rotate an existing application's key blindly. The old
ensure-app-key.php helper is retained for an explicitly approved initial setup.

The HTTP smoke test checks /, /contact and /login for HTTP 200, as in successful
release 936fd9e. It does not require Basic credentials, noindex or release headers.
The deployment validates the packaged REVISION on the server; HTTP success alone
does not prove a specific application SHA or a logged-in partner/admin workflow.

## Verification

Local Windows backend tests:

    php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit

Run npm run build for frontend changes. CI uses PHP 8.2.31.
The Linux deployment test runs as sudo bash tests/Deployment/remote.sh on an
isolated runner. Its nine filesystem scenarios simulate PHP/Artisan, not a real
database. SQLite tests do not prove MySQL migration compatibility.

## Recovery and remaining work

Code snapshots are in /home/USER/ebtest/.deploy-backups/TIMESTAMP-SHA, with
checksums. They exclude .env, database data, uploaded media and runtime storage.
They are not a database/media backup, and no automatic retention deletion runs.

After a failure, stop further deploys and inspect the private preflight.log or
application log. Do not print credentials, environment files or customer data.
Preflight failure leaves the current code untouched. Failure after maintenance
starts keeps the application down for diagnosis; do not blindly rerun migrations.

For a manual restore, acquire the same .deploy.lock and verify the chosen
snapshot's checksums and compatibility with the current database schema.
Restore the matching app/public code while preserving .env, storage and
.well-known. Recreate the storage symlink and public binding, clear stale caches,
rebuild config/views, validate the application, then bring it up and repeat HTTP
and authenticated checks. Do not blindly run migrate:rollback.

Actual DB/media restore drills, environment isolation, anonymized data,
server-level media protection, pinned host keys and partner/admin smoke tests
remain separately recorded work. D14 defers new infrastructure prerequisites;
it does not establish that these checks passed. Do not claim otherwise.

For HTTP 500, inspect /home/dekorhu/ebtest/myapp/storage/logs/laravel.log or the
daily Laravel log, then the cPanel PHP error log and document root. Keep debug
output off on public pages; review logs privately before sharing any excerpt.
