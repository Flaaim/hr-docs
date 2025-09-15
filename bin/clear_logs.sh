#!/bin/bash

# Переходим в директорию проекта
cd "$(dirname "$0")/.."

# Очищаем логи (сохраняем последние 100 строк)
for log_file in var/logs/*.log; do
  echo "$(tail -n 100 "$log_file")" > "$log_file"
done

# Логируем действие
echo "[$(date)] Logs were cleaned" >> var/logs/maintenance.log
