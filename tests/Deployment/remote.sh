#!/usr/bin/env bash
set -euo pipefail

# Linux filesystem integration test. PHP commands are simulated; no database or SSH.
root=$(cd "$(dirname "$0")/../.." && pwd)
home=$(mktemp -d /home/eb-deploy-test-XXXXXX)
trap '[[ $home == /home/eb-deploy-test-* ]] && rm -rf -- "$home"' EXIT
revision=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
base="$home/ebtest"
app="$base/myapp"
public="$base/public_html"
incoming="$base/.deploy-incoming/$revision"
export TRACE_FILE="$home/commands.log"

cat >"$home/fake-php" <<'BASH'
#!/usr/bin/env bash
set -euo pipefail
printf '%s|%s\n' "$PWD" "$*" >> "$TRACE_FILE"
case "$1" in
  scripts/deploy/configure-path.php)
    [[ ${FAIL_CONFIGURE:-0} != 1 ]] || exit 1
    printf '%s' "$DEPLOY_PATH" > public/.laravel-path.php ;;
  scripts/deploy/check.php)
    grep -qx 'APP_KEY=unchanged-test-key' .env
    [[ $# == 2 ]]
    [[ $(cat "$2/.laravel-path.php") == "$PWD" ]] ;;
  artisan)
    case "$2" in
      down) mkdir -p storage/framework; touch storage/framework/down ;;
      migrate) [[ ${FAIL_MIGRATION:-0} != 1 ]] ;;
      up) rm -f storage/framework/down ;;
      config:cache) touch bootstrap/cache/config.php ;;
    esac ;;
  *) exit 1 ;;
esac
BASH
chmod +x "$home/fake-php"

prepare() {
  [[ $base == "$home/ebtest" ]] || exit 1
  rm -rf -- "$base" "$home/release"
  : > "$TRACE_FILE"
  mkdir -p "$app/app" "$app/storage/app/public" "$app/storage/framework" "$public/.well-known" "$incoming"
  printf 'old-code' > "$app/app/old.php"
  printf 'old-public' > "$public/old.js"
  printf 'keep-media' > "$app/storage/app/public/photo.jpg"
  printf 'keep-challenge' > "$public/.well-known/token"
  printf 'APP_ENV=local\nAPP_KEY=unchanged-test-key\n' > "$app/.env"
  touch "$app/artisan"
  ln -s "$app/storage/app/public" "$public/storage"
  mkdir -p "$home/release"/{app,bootstrap/cache,config,database,lang,resources,routes,scripts/deploy,vendor,public/build}
  cp "$root/scripts/deploy/package.sh" "$home/release/scripts/deploy/"
  printf '%s' "$revision" > "$home/release/REVISION"
  printf 'new-code' > "$home/release/app/new.php"
  printf 'new-public' > "$home/release/public/index.php"
  printf '%s' "$app" > "$home/release/public/.laravel-path.php"
  printf '{}' > "$home/release/public/build/manifest.json"
  touch "$home/release/artisan" "$home/release/composer.json" "$home/release/composer.lock"
  bash "$home/release/scripts/deploy/package.sh" "$incoming"
}

deploy() {
  bash "$root/scripts/deploy/remote.sh" "$app" "$public" "$revision" "$home/fake-php" >"$home/result.log" 2>&1
}

assert_untouched() {
  [[ $(cat "$app/app/old.php") == old-code ]]
  [[ $(cat "$public/old.js") == old-public ]]
  [[ $(cat "$app/storage/app/public/photo.jpg") == keep-media ]]
  [[ ! -f "$app/storage/framework/down" ]]
  ! grep -q 'artisan down' "$TRACE_FILE"
}

assert_private_cleanup() {
  [[ ! -e "$incoming/env.deploy" ]]
  [[ -z $(find "$incoming" -maxdepth 1 -name 'preflight.*' -type d -print) ]]
}

prepare
cp "$app/.env" "$home/original.env"
deploy
cmp "$home/original.env" "$app/.env"
[[ $(cat "$app/app/new.php") == new-code && ! -e "$app/app/old.php" ]]
[[ $(cat "$public/index.php") == new-public && ! -e "$public/old.js" ]]
[[ $(cat "$public/storage/photo.jpg") == keep-media ]]
[[ $(cat "$public/.well-known/token") == keep-challenge ]]
[[ ! -f "$app/storage/framework/down" ]]
preflight_line=$(grep -n 'preflight.*scripts/deploy/check.php' "$TRACE_FILE" | head -1 | cut -d: -f1)
down_line=$(grep -n 'artisan down' "$TRACE_FILE" | cut -d: -f1)
[[ $preflight_line -lt $down_line ]]
backup=$(find "$base/.deploy-backups" -mindepth 1 -maxdepth 1 -type d)
(cd "$backup" && sha256sum --check SHA256SUMS)
mkdir -p "$home/restored/app" "$home/restored/public"
tar -xzf "$backup/app.tgz" -C "$home/restored/app"
tar -xzf "$backup/public.tgz" -C "$home/restored/public"
[[ $(cat "$home/restored/app/app/old.php") == old-code ]]
[[ $(cat "$home/restored/public/old.js") == old-public ]]
[[ ! -e "$home/restored/app/.env" && ! -e "$home/restored/app/storage" ]]
assert_private_cleanup
echo 'PASS: ordered preflight, deployment, preservation and code snapshot unpacking'

prepare
printf 'APP_ENV=production\n' > "$app/.env"
cp "$app/.env" "$incoming/env.deploy"
if deploy; then exit 1; fi
assert_untouched
assert_private_cleanup
echo 'PASS: invalid environment rejected before maintenance'

prepare
if FAIL_CONFIGURE=1 deploy; then exit 1; fi
assert_untouched
assert_private_cleanup
echo 'PASS: path configuration failure cannot be swallowed by the preflight subshell'

prepare
printf 'corrupt' >> "$incoming/app.tgz"
if deploy; then exit 1; fi
assert_untouched
echo 'PASS: checksum mismatch rejected before maintenance'

prepare
printf 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb' > "$home/release/REVISION"
bash "$home/release/scripts/deploy/package.sh" "$incoming"
if deploy; then exit 1; fi
assert_untouched
assert_private_cleanup
echo 'PASS: wrong release revision rejected'

prepare
rm "$public/storage"
mkdir "$public/storage"
printf 'unmigrated' > "$public/storage/photo.jpg"
if deploy; then exit 1; fi
assert_untouched
[[ $(cat "$public/storage/photo.jpg") == unmigrated ]]
echo 'PASS: real public storage is never deleted'

prepare
mv "$app/storage" "$home/shared-storage"
ln -s "$home/shared-storage" "$app/storage"
if deploy; then exit 1; fi
assert_untouched
echo 'PASS: shared runtime storage is refused'

prepare
if flock "$base/.deploy.lock" bash "$root/scripts/deploy/remote.sh" "$app" "$public" "$revision" "$home/fake-php" >"$home/result.log" 2>&1; then exit 1; fi
assert_untouched
echo 'PASS: concurrent deploy is refused'

prepare
if FAIL_MIGRATION=1 deploy; then exit 1; fi
[[ ! -f "$app/storage/framework/down" ]]
[[ $(cat "$app/app/new.php") == new-code && ! -e "$app/app/old.php" ]]
[[ -n $(find "$base/.deploy-backups" -name app.tgz -print) ]]
[[ $(cat "$app/storage/app/public/photo.jpg") == keep-media ]]
assert_private_cleanup
echo 'PASS: failed migration restores service mode and retains the code snapshot'
