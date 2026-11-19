#!/bin/sh
set -eu

port="${PORT:-10000}"

# Ensure exactly one MPM (prefork, required by mod_php) is enabled.
for f in /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf; do
  case "$f" in
    */mpm_prefork.*) ;;
    *) rm -f "$f" ;;
  esac
done
sed -i "s/^Listen 80$/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
