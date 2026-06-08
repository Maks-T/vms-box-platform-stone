#!/bin/bash

# 1. Читаем параметры
TARGET_DATE=${1:-"2026-04-26"}
EXCLUDE_STR=$2 # Получаем строку исключений целиком

OUTPUT_DIR="git_diff"
mkdir -p "$OUTPUT_DIR"

# 2. Безопасно разбиваем строку исключений на массив
read -ra EXCLUDE_PATTERNS <<< "$EXCLUDE_STR"

# Формируем аргументы исключения для git diff
exclude_args=()
for pattern in "${EXCLUDE_PATTERNS[@]}"; do
    exclude_args+=(":(exclude)$pattern")
done

# 3. Получаем коммиты
# ВАРИАНТ А: Если нужны коммиты НАЧИНАЯ с этой даты и до сегодня:
GIT_LOG_CMD=(git log --since="$TARGET_DATE 00:00:00" --reverse --date=short --pretty=format:"%H %h %ad")

# ВАРИАНТ Б: Если нужны коммиты строго ДО этой даты (включая саму дату до конца дня), закомментируй строку выше и раскомментируй эту:
# GIT_LOG_CMD=(git log --before="$TARGET_DATE 23:59:59" --reverse --date=short --pretty=format:"%H %h %ad")

mapfile -t commits_info < <("${GIT_LOG_CMD[@]}")
count=${#commits_info[@]}

if [ "$count" -lt 2 ]; then
    echo "Недостаточно коммитов (найдено $count). Нужно хотя бы 2."
    exit 1
fi

echo "Найдено $count коммитов. Генерация diff файлов..."

# Читаем данные самого первого коммита
read -r prev_full_hash prev_short_hash prev_date <<< "${commits_info[0]}"

for ((i=1; i<count; i++)); do
    read -r curr_full_hash curr_short_hash curr_date <<< "${commits_info[$i]}"

    # Формируем имя файла
    filename="diff_${curr_date}_${curr_short_hash}.txt"

    # Выполняем diff
    git diff "$prev_full_hash" "$curr_full_hash" -- . "${exclude_args[@]}" > "${OUTPUT_DIR}/${filename}"

    echo "Создан: $filename"

    # Сдвигаем указатель (коммит 1->2, 2->3 и т.д.)
    prev_full_hash="$curr_full_hash"
done

echo "Готово. Исключены: ${EXCLUDE_PATTERNS[*]:-(ничего)}"