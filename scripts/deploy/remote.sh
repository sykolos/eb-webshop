#!/usr/bin/env bash
set -euo pipefail
umask 077

app=${1:?Application path required}
public=${2:?Public path required}
revision=${3:?Release revision required}
php_bin=${4:-php}
[[ $revision =~ ^[a-f0-9]{40}$ ]] || exit 1
[[ $php_bin =~ ^[a-zA-Z0-9_./-]+$ ]] || exit 1

for path in "$app" "$public"; do
  [[ $path =~ ^/home/[a-zA-Z0-9_-]+/ebtest/[a-zA-Z0-9_-]+$ ]] || {
    echo 'Deployment paths must be sibling directories inside /home/USER/ebtest.' >&2; exit 1;
  }
  [[ $(realpath -m "$path") == "$path" ]] || { echo 'Symlinked deployment path refused.' >&2; exit 1; }
done
[[ $app != "$public" && $(dirname "$app") == "$(dirname "$public")" ]] || exit 1
base=$(dirname "$app")
incoming="$base/.deploy-incoming/$revision"
for path in "$incoming" "$base/.deploy-backups" "$app/storage" "$app/storage/app/public" "$app/storage/framework" "$app/storage/logs"; do
  [[ $(realpath -m "$path") == "$path" ]] || { echo 'Symlinked runtime or release path refused.' >&2; exit 1; }
done
[[ ! -L "$base/.deploy.lock" ]] || exit 1
mkdir -p "$app" "$public" "$base/.deploy-backups"
exec 9>"$base/.deploy.lock"
flock -n 9 || { echo 'Another deployment is active.' >&2; exit 1; }
cd "$incoming"
candidate=''
backup='not-created'
maintenance_started=false
replacement_started=false
cleanup() {
  code=$?
  trap - EXIT
  rm -f -- "$incoming/env.deploy"
  if [[ -n $candidate && $candidate == "$incoming"/preflight.* ]]; then
    rm -rf -- "$candidate"
  fi
  if [[ $code -ne 0 ]]; then
    echo "Deployment failed; backup: $backup" >&2
    if $maintenance_started; then
      echo 'Maintenance retained. Inspect the failure before restoring code or migrating again.' >&2
    elif $replacement_started; then
      echo 'Initial installation is incomplete; inspect before retrying.' >&2
    else
      echo 'Stopped before live code replacement.' >&2
    fi
  fi
  exit "$code"
}
trap cleanup EXIT
sha256sum --check SHA256SUMS
tar -tzf app.tgz >/dev/null
tar -tzf public.tgz >/dev/null
[[ ! -e "$public/storage" || -L "$public/storage" ]] || {
  echo 'public/storage is a real directory; migrate uploads before deployment.' >&2; exit 1;
}
test ! -f "$app/storage/framework/down" || { echo 'Existing maintenance state; inspect before deploying.' >&2; exit 1; }

# Boot the candidate with a private copy of the actual environment before downtime.
environment="$app/.env"
if [[ ! -f "$environment" ]]; then environment="$incoming/env.deploy"; fi
test -r "$environment" || { echo 'Missing staging environment.' >&2; exit 1; }
candidate=$(mktemp -d "$incoming/preflight.XXXXXX")
tar -C "$candidate" -xzf app.tgz
[[ $(cat "$candidate/REVISION") == "$revision" ]] || { echo 'Archive revision does not match requested release.' >&2; exit 1; }
mkdir -p "$candidate/public" "$candidate"/storage/{app/public,framework/{cache,sessions,testing,views},logs} "$candidate/bootstrap/cache"
tar -C "$candidate/public" -xzf public.tgz
install -m 600 "$environment" "$candidate/.env"
if ! (
  cd "$candidate" || exit 1
  DEPLOY_PATH="$candidate" PUBLIC_PATH="$candidate/public" "$php_bin" scripts/deploy/configure-path.php || exit 1
  "$php_bin" scripts/deploy/check.php "$candidate/public"
) >"$incoming/preflight.log" 2>&1; then
  echo 'Candidate validation failed; inspect the private preflight.log. Live code was not changed.' >&2
  exit 1
fi
echo 'Candidate environment and application validated before maintenance.'

backup="$base/.deploy-backups/$(date -u +%Y%m%dT%H%M%SZ)-$revision"
mkdir "$backup"
# These are code snapshots. DB/uploads require the independent backup/restore procedure.
tar -C "$app" -czf "$backup/app.tgz" --exclude='./.env*' --exclude='./storage' --exclude='./bootstrap/cache/*.php' .
tar -C "$public" -czf "$backup/public.tgz" --exclude='./storage' --exclude='./.well-known' .
(cd "$backup" && sha256sum app.tgz public.tgz > SHA256SUMS)

if [[ ! -f "$app/.env" ]]; then
  install -m 600 "$candidate/.env" "$app/.env"
fi
if [[ -f "$app/artisan" ]]; then
  (cd "$app" && "$php_bin" artisan down --render=errors::503 --retry=60)
  maintenance_started=true
fi

# Remove stale runtime code, but never environment, uploads or runtime storage.
replacement_started=true
for entry in app bootstrap config database lang resources routes scripts/deploy vendor artisan composer.json composer.lock REVISION; do
  rm -rf -- "${app:?}/$entry"
done
tar -C "$app" -xzf app.tgz
mkdir -p "$app"/storage/{app/public,framework/{cache,sessions,testing,views},logs} "$app/bootstrap/cache"
rm -f "$app/bootstrap/cache/config.php" "$app"/bootstrap/cache/routes-*.php "$app/bootstrap/cache/events.php"

# Validated, fixed staging path; preserve uploads and certificate challenge files.
find "$public" -mindepth 1 -maxdepth 1 ! -name storage ! -name .well-known ! -name index.php ! -name .htaccess ! -name .laravel-path.php -exec rm -rf -- {} +
tar -C "$public" -xzf public.tgz
find "$public" -type d -exec chmod 755 {} +
find "$public" -type f -exec chmod 644 {} +
chmod 755 "$public"
ln -sfn "$app/storage/app/public" "$public/storage"
cd "$app"
"$php_bin" scripts/deploy/check.php "$public"
"$php_bin" artisan optimize:clear
"$php_bin" artisan migrate --force
"$php_bin" artisan config:cache
"$php_bin" artisan view:cache
"$php_bin" artisan up
echo "Release $revision installed. Code backup: $backup"
