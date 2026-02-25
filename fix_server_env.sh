#!/usr/bin/env bash
# Run this ON THE HOSTINGER SERVER via SSH to fix .env for production.
# Usage: ssh ... "bash -s" < fix_server_env.sh

set -e
cd /home/u473837995/domains/indigo-cormorant-820127.hostingersite.com/public_html

if [[ ! -f .env ]]; then
  echo "ERROR: .env not found"
  exit 1
fi

# Fix APP_URL
if grep -q '^APP_URL=' .env; then
  sed -i 's|^APP_URL=.*|APP_URL=https://indigo-cormorant-820127.hostingersite.com|' .env
  echo "Fixed APP_URL"
else
  echo "APP_URL=https://indigo-cormorant-820127.hostingersite.com" >> .env
  echo "Added APP_URL"
fi

# Add SESSION_SECURE_COOKIE if missing
if ! grep -q '^SESSION_SECURE_COOKIE=' .env; then
  echo "SESSION_SECURE_COOKIE=true" >> .env
  echo "Added SESSION_SECURE_COOKIE=true"
fi

php artisan config:clear
echo "Done. Test: https://indigo-cormorant-820127.hostingersite.com/"
