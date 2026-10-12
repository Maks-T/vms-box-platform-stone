#!/bin/bash
set -e # Останавливать выполнение при критических ошибках

BRANCH_NAME=${1:-"deploy/build"}
TARGET_WIDGET=${2:-"all"}
CONFIG_FILE="$(dirname "$0")/widgets.json"

if [ ! -f "$CONFIG_FILE" ]; then
    echo "✖ Файл конфигурации виджетов widgets.json не найден!"
    exit 1
fi

deploy_item() {
    local key="$1"
    local repo="$2"
    local target="$3"
    local branch="${BRANCH_NAME:-$4}"

    echo "===> Развёртывание виджета [$key] (Ветка: $branch, Путь: $target)..."
    rm -rf "$target" || true
    git clone -b "$branch" --single-branch "$repo" "$target"
    rm -rf "$target/.git" || true
    if [ "$(id -u)" -eq 0 ]; then
        chown -R www-data:www-data "$target" 2>/dev/null || true
        chmod -R 775 "$target" 2>/dev/null || true
    fi
    echo "✔ Виджет [$key] успешно развернут в $target."
}

if [ "$TARGET_WIDGET" = "all" ]; then
    echo "===> Развёртывание всех виджетов из widgets.json..."
    for key in $(jq -r 'keys[]' "$CONFIG_FILE"); do
        repo=$(jq -r ".[\"$key\"].repo" "$CONFIG_FILE")
        target=$(jq -r ".[\"$key\"].target" "$CONFIG_FILE")
        def_branch=$(jq -r ".[\"$key\"].branch // \"deploy/build\"" "$CONFIG_FILE")
        deploy_item "$key" "$repo" "$target" "$def_branch"
    done
else
    if jq -e ".[\"$TARGET_WIDGET\"]" "$CONFIG_FILE" >/dev/null 2>&1; then
        repo=$(jq -r ".[\"$TARGET_WIDGET\"].repo" "$CONFIG_FILE")
        target=$(jq -r ".[\"$TARGET_WIDGET\"].target" "$CONFIG_FILE")
        def_branch=$(jq -r ".[\"$TARGET_WIDGET\"].branch // \"deploy/build\"" "$CONFIG_FILE")
        deploy_item "$TARGET_WIDGET" "$repo" "$target" "$def_branch"
    else
        echo "✖ Виджет [$TARGET_WIDGET] не зарегистрирован в widgets.json."
        exit 1
    fi
fi

echo "Все операции развёртывания виджетов завершены."
