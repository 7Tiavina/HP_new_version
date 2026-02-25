#!/bin/bash
# Remove old (disabled) snap revisions to free disk space

set -e
echo "Removing old snap revisions..."

sudo snap remove core22 --revision=1748
sudo snap remove firefox --revision=5751
sudo snap remove firmware-updater --revision=167
sudo snap remove gnome-42-2204 --revision=202
sudo snap remove snap-store --revision=1248
sudo snap remove snapd --revision=23545
sudo snap remove snapd-desktop-integration --revision=253

echo "Done. Old snap revisions removed."
df -h /
