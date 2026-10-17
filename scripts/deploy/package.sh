#!/usr/bin/env bash
set -euo pipefail

output_dir=${1:?Package output directory required}
[[ $output_dir = /* ]] || { echo 'Package output must be absolute.' >&2; exit 1; }
root=$(cd "$(dirname "$0")/../.." && pwd)
cd "$root"
[[ $(cat REVISION) =~ ^[a-f0-9]{40}$ ]] || { echo 'Missing release revision.' >&2; exit 1; }
test -f public/build/manifest.json
mkdir -p "$output_dir"

# Only runtime files enter the release, irrespective of local/untracked reference files.
app_entries=(app bootstrap config database lang resources routes scripts/deploy vendor artisan composer.json composer.lock REVISION)
tar -czf "$output_dir/app.tgz" --exclude='bootstrap/cache/*.php' "${app_entries[@]}"
tar -C public -czf "$output_dir/public.tgz" --exclude='./storage' --exclude='./hot' --exclude='./.well-known' .
(cd "$output_dir" && sha256sum app.tgz public.tgz > SHA256SUMS)
