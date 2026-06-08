import json

# Опция: список языков, которые нужно ОСТАВИТЬ.
# Все остальные ключи в объектах перевода будут удалены.
LANGUAGES_TO_KEEP = ['ru', 'en']

def filter_languages(node):
    """Рекурсивно обходит структуру JSON и удаляет лишние переводы."""
    if isinstance(node, dict):
        # Если в словаре есть ключ "ru" с текстовым значением,
        # считаем, что это объект с переводами
        if "ru" in node and isinstance(node["ru"], str):
            # Собираем список ключей (языков), которых нет в разрешенном списке
            keys_to_remove = [k for k in node.keys() if k not in LANGUAGES_TO_KEEP]

            # Удаляем лишние ключи (например, 'kk')
            for k in keys_to_remove:
                del node[k]

        # Рекурсивно идем глубже по всем значениям
        for value in node.values():
            filter_languages(value)

    elif isinstance(node, list):
        for item in node:
            filter_languages(item)

def main():
    # Пути к вашим файлам
    input_file = r'\\wsl.localhost\Ubuntu-24.04\home\maks-t\vms-box-platform-stone\import\import_ready_multilingual.json'
    output_file = r'\\wsl.localhost\Ubuntu-24.04\home\maks-t\vms-box-platform-stone\import\import_ready_filtered.json'

    print("Загрузка файла...")
    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            data = json.load(f)
    except FileNotFoundError:
        print(f"Ошибка: Файл '{input_file}' не найден.")
        return

    print(f"Фильтрация. Оставляем только языки: {LANGUAGES_TO_KEEP}...")
    filter_languages(data)

    print("Сохранение результата...")
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=4)

    print(f"Готово! Очищенный файл сохранен:\n{output_file}")

if __name__ == "__main__":
    main()
