#!/usr/bin/env python3
"""
Анализирует все вызовы __('...') и @lang('...') в PHP и Blade файлах.
Поддерживает многострочное форматирование и работу с несколькими пакетами.
"""

import os
import re
import json
import sys

# ANSI escape codes для цвета
GREEN = '\033[92m'
YELLOW = '\033[93m'
BLUE = '\033[94m'
RED = '\033[91m'
CYAN = '\033[96m'
RESET = '\033[0m'

# Настройка модулей проекта (какие папки сканировать и с каким словарем сверять)
MODULES = [
    {
        "name": "Пакет: Nicole Core",
        "scan_dirs": ["packages/box/nicole/core"],
        "lang_file": "packages/box/nicole/core/lang/en.json"
    },
    {
        "name": "Пакет: Valerie Industry Stone",
        "scan_dirs": ["packages/box/valerie/industry-stone"],
        "lang_file": "packages/box/valerie/industry-stone/lang/en.json"
    },
    {
        "name": "Основное приложение (App + Resources)",
        "scan_dirs": ["app", "resources"],
        "lang_file": "lang/en.json" # Стандартная директория языков в Laravel 9+
    }
]

def find_php_files(base_path, relative_dirs):
    """Рекурсивно ищет .php (в т.ч. .blade.php) файлы в указанных директориях."""
    php_files = []
    for rel_dir in relative_dirs:
        target_dir = os.path.join(base_path, rel_dir)
        if not os.path.exists(target_dir):
            print(f"{RED}Внимание: Папка {target_dir} не найдена.{RESET}")
            continue

        for dirpath, _, filenames in os.walk(target_dir):
            for fname in filenames:
                if fname.endswith('.php'):
                    php_files.append(os.path.join(dirpath, fname))
    return php_files

def extract_translation_strings(file_path):
    """
    Извлекает строки из __() и @lang().
    Игнорирует то, что идет после кавычки (запятые, скобки).
    """
    strings = set()
    # \1 - захватывает тип кавычки (' или ")
    # \2 - захватывает сам текст
    # (?<!\\)\1 - проверяет, что закрывающая кавычка не экранирована
    pattern = re.compile(r"(?:__|@lang)\(\s*(['\"])(.*?)(?<!\\)\1", re.DOTALL)

    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()

        matches = pattern.findall(content)
        for quote, text in matches:
            # Убираем экранирование кавычек внутри строки, если оно было
            clean_text = text.replace(f"\\{quote}", quote)
            strings.add(clean_text)

    except Exception as e:
        print(f"{RED}Ошибка чтения {file_path}: {e}{RESET}")

    return strings

def load_existing_keys(json_path):
    """Загружает существующие ключи из JSON-словаря."""
    if not os.path.exists(json_path):
        return set(), {}
    try:
        with open(json_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
        return set(data.keys()), data
    except Exception as e:
        print(f"{RED}Ошибка парсинга {json_path}: {e}{RESET}")
        return set(), {}

def process_module(module, base_path):
    """Обрабатывает один конкретный модуль."""
    print(f"\n{CYAN}============================================================{RESET}")
    print(f"{CYAN} АНАЛИЗ: {module['name']}{RESET}")
    print(f"{CYAN}============================================================{RESET}")

    php_files = find_php_files(base_path, module["scan_dirs"])
    if not php_files:
        print(f"{YELLOW}Файлы для сканирования не найдены. Пропускаем.{RESET}")
        return

    # Собираем фразы из кода
    all_strings = set()
    for file in php_files:
        all_strings.update(extract_translation_strings(file))

    # Сверяем с JSON
    json_path = os.path.join(base_path, module["lang_file"])
    existing_keys, json_data = load_existing_keys(json_path)

    missing = all_strings - existing_keys
    unused = existing_keys - all_strings

    # ВЫВОД: ОТСУТСТВУЮТ В JSON
    if missing:
        print(f"{GREEN}Фразы, которые нужно ДОБАВИТЬ в {os.path.basename(json_path)}:{RESET}")
        for phrase in sorted(missing):
            # Заменяем переносы строк на \n для корректного JSON
            safe_phrase = phrase.replace('\n', '\\n').replace('"', '\\"')
            print(f'  "{safe_phrase}": "{safe_phrase}",')
    else:
        print(f"{GREEN}✔ Все фразы из кода присутствуют в словаре.{RESET}")

    print("-" * 60)

    # ВЫВОД: НЕ ИСПОЛЬЗУЮТСЯ
    if unused:
        print(f"{YELLOW}Фразы из {os.path.basename(json_path)}, которые НЕ НАЙДЕНЫ в коде:{RESET}")
        for phrase in sorted(unused):
            translation = json_data.get(phrase, phrase)
            if translation != phrase:
                print(f'  "{phrase}" → "{translation}"')
            else:
                print(f'  "{phrase}"')
    elif existing_keys:
        print(f"{YELLOW}✔ Все ключи из словаря используются в коде.{RESET}")
    else:
        print(f"{YELLOW}Словарь пока пуст или не создан.{RESET}")

    # СТАТИСТИКА
    print("-" * 60)
    print(f"{BLUE}Статистика модуля:{RESET}")
    print(f"  Просканировано файлов: {len(php_files)}")
    print(f"  Найдено фраз в коде:   {len(all_strings)}")
    print(f"  Отсутствует в JSON:    {len(missing)}")
    print(f"  Лишних ключей в JSON:  {len(unused)}\n")


def main():
    # Если путь не передан, берем текущую директорию (корень проекта)
    if len(sys.argv) > 1:
        base_path = sys.argv[1]
    else:
        base_path = os.getcwd()

    if not os.path.isdir(base_path) or not os.path.exists(os.path.join(base_path, "artisan")):
        print(f"{RED}Ошибка: Скрипт нужно запускать из корня Laravel проекта!{RESET}")
        print(f"Текущий путь: {base_path}")
        sys.exit(1)

    print(f"{BLUE}Запуск сканирования переводов...{RESET}")
    print(f"{BLUE}Корень проекта: {base_path}{RESET}")

    for module in MODULES:
        process_module(module, base_path)

if __name__ == "__main__":
    main()
