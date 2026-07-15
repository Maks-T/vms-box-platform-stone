#!/bin/bash
set -e # Останавливать выполнение при любых ошибках

BRANCH_NAME=${1:-"deploy/build"}

echo "Обновление статических файлов виджета калькулятора (Ветка: $BRANCH_NAME)..."

rm -rf public/cpq-stone

git clone -b "$BRANCH_NAME" --single-branch git@github.com:kapitulin24/cpq-stone-calc.gitpublic/cpq-stone

rm -rf public/widget/.git

chown -R www-data:www-data public/cpq-stone
chmod -R 775 public/cpq-stone

echo "Виджет успешно обновлен."
