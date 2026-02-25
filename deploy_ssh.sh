#!/bin/bash
# Deploy to SSH server. Run from project root. You will be prompted for the SSH password.
# Usage:
#   ./deploy_ssh.sh          — deploy to REMOTE_PATH below
#   ./deploy_ssh.sh list     — list server home dir to find where to upload (then set REMOTE_PATH)
#   ./deploy_ssh.sh ~/domains/mydomain.com/public_html  — deploy to this path
set -e
SSH_HOST="u473837995@109.106.242.158"
SSH_PORT="65002"
SSH_OPTS="-p $SSH_PORT -o StrictHostKeyChecking=no"

# Where to upload. Common: ~/public_html  or  ~/domains/YOURDOMAIN.com/public_html
REMOTE_PATH="${1:-~/public_html}"

if [ "$REMOTE_PATH" = "list" ]; then
  echo "Listing home directory on server (enter password when prompted)..."
  ssh $SSH_OPTS "$SSH_HOST" "echo '=== Home ===' && ls -la && echo '' && echo '=== public_html (if exists) ===' && ls -la public_html 2>/dev/null || echo 'No public_html' && echo '' && echo '=== domains (if exists) ===' && ls -la domains 2>/dev/null || echo 'No domains'"
  echo ""
  echo "Set REMOTE_PATH in this script or run: ./deploy_ssh.sh ~/path/you/saw/above"
  exit 0
fi

echo "Deploying to ${SSH_HOST}:${REMOTE_PATH}"
echo "Enter SSH password when prompted."
rsync -avz --progress \
  -e "ssh $SSH_OPTS" \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude 'storage/logs/*' \
  --exclude 'storage/framework/cache/*' \
  --exclude 'storage/framework/sessions/*' \
  --exclude 'storage/framework/views/*' \
  --exclude '*.zip' \
  --exclude '.env' \
  ./ "${SSH_HOST}:${REMOTE_PATH}/"

echo "Done. Configure .env on the server if needed."
