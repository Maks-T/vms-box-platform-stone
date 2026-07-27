#!/bin/bash
set -e # Останавливать выполнение при критических ошибках

BRANCH_NAME=${1:-"deploy/build"}

echo "Обновление статических файлов виджета калькулятора (Ветка: $BRANCH_NAME)..."

rm -rf public/cpq-stone || true

git clone -b "$BRANCH_NAME" --single-branch git@github.com:kapitulin24/cpq-stone-calc.git public/cpq-stone

rm -rf public/cpq-stone/.git || true

chown -R www-data:www-data public/cpq-stone || true
chmod -R 775 public/cpq-stone || true

echo "Виджет успешно обновлен."
