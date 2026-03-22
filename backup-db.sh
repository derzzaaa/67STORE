#!/bin/bash
# ============================================================
# backup-db.sh — Резервное копирование базы данных
# ============================================================
# Использование: bash backup-db.sh
# Рекомендуется добавить в crontab:
#   0 2 * * * /var/www/html/backup-db.sh >> /var/log/backup.log 2>&1

# ── Настройки (берём из .env или задаём вручную) ──────────────────────────
if [ -f "$(dirname "$0")/.env" ]; then
    source "$(dirname "$0")/.env"
fi

DB_HOST="${DB_HOST:-localhost}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_NAME="${DB_NAME:-seven_eleven_shop}"

BACKUP_DIR="$(dirname "$0")/backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/db_backup_${DATE}.sql.gz"

# ── Создаём папку для бэкапов ─────────────────────────────────────────────
mkdir -p "$BACKUP_DIR"

# ── Выполняем дамп ────────────────────────────────────────────────────────
echo "[$(date)] Starting database backup..."

mysqldump \
    --host="$DB_HOST" \
    --user="$DB_USER" \
    --password="$DB_PASS" \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    "$DB_NAME" | gzip > "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "[$(date)] Backup successful: $BACKUP_FILE"
    echo "[$(date)] File size: $(du -h "$BACKUP_FILE" | cut -f1)"
else
    echo "[$(date)] ERROR: Backup failed!" >&2
    exit 1
fi

# ── Удаляем бэкапы старше 30 дней ─────────────────────────────────────────
find "$BACKUP_DIR" -name "db_backup_*.sql.gz" -mtime +30 -delete
echo "[$(date)] Old backups cleaned up."
echo "----------------------------------------------------"
