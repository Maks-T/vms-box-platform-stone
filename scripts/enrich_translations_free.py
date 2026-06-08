import json
import asyncio
import time
import random  # Добавлен модуль для генерации случайных чисел
import translators as ts
from functools import partial

# Целевые языки: английский и казахский (исходный — русский)
TARGET_LOCALES = ['en', 'kk']

# Строгое ограничение: максимум 2 одновременных запроса
API_SEMAPHORE = asyncio.Semaphore(2)

def fetch_translation(text: str, lang: str) -> str:
    """Синхронный запрос к API переводчика."""
    try:
        # Плавающая пауза от 1.5 до 3.5 секунд для защиты от бана IP
        sleep_time = random.uniform(1.5, 3.5)
        time.sleep(sleep_time)

        return ts.translate_text(
            query_text=text,
            translator='google',
            from_language='ru',
            to_language=lang
        )
    except Exception as e:
        print(f"      [!] Ошибка перевода '{text[:20]}...' на {lang}: {e}")
        return text

async def translate_text(text: str) -> dict:
    """Асинхронный запуск переводов на все целевые языки."""
    if not text or not text.strip():
        return {lang: "" for lang in TARGET_LOCALES}

    translations = {}
    loop = asyncio.get_running_loop()

    # Запускаем переводы конкурентно, но ограничиваем семафором
    async with API_SEMAPHORE:
        print(f"Перевожу: {text[:50]}...")

        # Дополнительная пауза между целыми блоками текста
        await asyncio.sleep(random.uniform(0.5, 1.5))

        tasks = []
        for lang in TARGET_LOCALES:
            func = partial(fetch_translation, text, lang)
            tasks.append(loop.run_in_executor(None, func))

        results = await asyncio.gather(*tasks)

        for lang, translated_text in zip(TARGET_LOCALES, results):
            translations[lang] = translated_text

    return translations

async def process_json(data):
    """Обходит JSON и собирает задачи на перевод."""
    tasks = []

    async def translate_and_update(target_dict, text):
        translations = await translate_text(text)
        # Добавляем новые языки в существующий словарь
        target_dict.update(translations)

    def collect_tasks(node):
        if isinstance(node, dict):
            # Если это объект перевода (содержит строковый ключ 'ru')
            if "ru" in node and isinstance(node["ru"], str):
                # Проверяем, не переведен ли он уже (полезно при перезапуске скрипта)
                if not all(lang in node for lang in TARGET_LOCALES):
                    tasks.append(translate_and_update(node, node["ru"]))

            # Продолжаем обход вглубь
            for value in node.values():
                collect_tasks(value)

        elif isinstance(node, list):
            for item in node:
                collect_tasks(item)

    print("Анализ JSON структуры...")
    collect_tasks(data)

    print(f"Найдено строк для перевода: {len(tasks)}")
    if tasks:
        print("Начинаем перевод. Процесс пойдет медленно для защиты от блокировки IP...")
        # Запускаем все собранные задачи
        await asyncio.gather(*tasks)

async def main():
    input_file = r'\\wsl.localhost\Ubuntu-24.04\home\maks-t\vms-box-platform-stone\import\import_ready.json'
    output_file = r'\\wsl.localhost\Ubuntu-24.04\home\maks-t\vms-box-platform-stone\import\import_ready_multilingual.json'

    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            data = json.load(f)
    except FileNotFoundError:
        print(f"Ошибка: Файл '{input_file}' не найден.")
        return

    try:
        await process_json(data)
    except KeyboardInterrupt:
        print("\nПроцесс прерван пользователем! Сохраняем то, что успели перевести...")
    except Exception as e:
         print(f"\nСкрипт упал с ошибкой: {e}. Сохраняем прогресс...")
    finally:
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=4)

        print(f"Готово! Результат сохранен в:\n{output_file}")

if __name__ == "__main__":
    asyncio.run(main())
