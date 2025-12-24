#!/bin/sh
set -e
host="$1"; shift
cmd="$@"
echo "⏳ Waiting for DB ($host) to be ready..."
export MYSQL_PWD="${DB_PASS}"
user="${DB_USER}"
until mysql --skip-ssl -h "$host" -u"$user" -e "SELECT 1;" >/dev/null 2>&1; do
  sleep 2
done
echo "✅ DB is ready, starting app..."
exec $cmd
