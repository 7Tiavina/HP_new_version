#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   ./deploy_hostinger.sh
#   ./deploy_hostinger.sh /home/u473837995/domains/other-domain.com/public_html
#
# Deploys to https://indigo-cormorant-820127.hostingersite.com by default.
# Uploads everything (no exclusions). APP_URL fixed on server after deploy.

REMOTE_USER="u473837995"
REMOTE_HOST="109.106.242.158"
REMOTE_PORT="65002"
SSH_KEY="${HOME}/.ssh/hellopassenger_deploy"

# Default: indigo-cormorant-820127.hostingersite.com
DEFAULT_REMOTE_PATH="/home/${REMOTE_USER}/domains/indigo-cormorant-820127.hostingersite.com/public_html"
REMOTE_PATH="${1:-$DEFAULT_REMOTE_PATH}"

if [[ ! -f "${SSH_KEY}" ]]; then
  echo "Missing SSH key: ${SSH_KEY}"
  echo "Create it with:"
  echo "  ssh-keygen -t ed25519 -C \"hellopassenger-deploy\" -f \"${SSH_KEY}\" -N \"\""
  exit 2
fi

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "${ROOT_DIR}"

echo "==> Uploading project to ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}"
rsync -az --delete --progress \
  --exclude ".git" \
  --exclude "node_modules" \
  --exclude "*.zip" \
  --exclude ".env" \
  --exclude ".htaccess" \
  --exclude "public/.htaccess" \
  -e "ssh -i \"${SSH_KEY}\" -p ${REMOTE_PORT}" \
  ./ "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/"

echo "==> Fixing server (remove default index.html, permissions)"
ssh -i "${SSH_KEY}" -p "${REMOTE_PORT}" "${REMOTE_USER}@${REMOTE_HOST}" "cd \"${REMOTE_PATH}\" && rm -f index.html 2>/dev/null; chmod -R 755 storage bootstrap/cache 2>/dev/null; chmod 644 .htaccess index.php public/.htaccess public/index.php 2>/dev/null; chmod -R 755 public 2>/dev/null || true"

echo "==> Running Laravel commands on server"
ssh -i "${SSH_KEY}" -p "${REMOTE_PORT}" "${REMOTE_USER}@${REMOTE_HOST}" "cd \"${REMOTE_PATH}\" && sed -i 's|^APP_URL=.*|APP_URL=https://indigo-cormorant-820127.hostingersite.com|' .env 2>/dev/null; mkdir -p storage/framework/views storage/framework/sessions storage/framework/cache storage/logs bootstrap/cache && chmod -R 755 storage bootstrap/cache && php artisan storage:link 2>/dev/null || true && php artisan config:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache 2>/dev/null || true"

echo "==> Done."

