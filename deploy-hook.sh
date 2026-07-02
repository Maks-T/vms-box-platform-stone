#!/bin/bash

echo "Загрузка и обновление виджета cpq-stone..."
rm -rf public/cpq-stone
git clone -b deploy/build --single-branch git@github.com:kapitulin24/cpq-stone-calc.git public/cpq-stone
rm -rf public/cpq-stone/.git
echo "Виджет cpq-stone успешно добавлен."
