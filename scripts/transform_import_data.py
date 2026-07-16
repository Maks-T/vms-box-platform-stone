# scripts/transform_import_data.py
import os
import json
import re
import sys

# ==============================================================================
# НАСТРОЙКИ КОНВЕРТАЦИИ И ИМПОРТА
# ==============================================================================
RENAME_IMAGES_TO_WEBP = True  # Если True, расширения всех изображений будут заменены на .webp

# Если True, все товары без каких-либо изображений (и у товара, и у всех его вариаций)
# будут автоматически импортироваться как неактивные (скрытые) [2]
HIDE_PRODUCTS_WITHOUT_IMAGES = True

# Определяем пути к файлам
BASE_DIR = "/home/maks-t/vms-box-platform-stone/import" if os.name == 'posix' else r"\\wsl.localhost\Ubuntu-24.04\home\maks-t\vms-box-platform-stone\import"
INPUT_JSON = os.path.join(BASE_DIR, "import_mebel_raw_ru_pic.json")
OUTPUT_JSON = os.path.join(BASE_DIR, "import_data.json")

# Константный список валют для инъекции
STATIC_CURRENCIES = [
    {
      "code": "BYN",
      "symbol": "Br",
      "symbol_native": {
        "ru": "руб.",
        "en": "Br"
      },
      "name": {
        "ru": "Белорусский рубль",
        "en": "Belarusian Ruble"
      },
      "rate": 1.0,
      "is_default": True,
      "is_active": True
    },
    {
      "code": "RUB",
      "symbol": "₽",
      "symbol_native": {
        "ru": "руб.",
        "en": "rub."
      },
      "name": {
        "ru": "Российский рубль",
        "en": "Russian Ruble"
      },
      "rate": 0.0339,
      "is_default": False,
      "is_active": True
    },
    {
      "code": "USD",
      "symbol": "$",
      "symbol_native": {
        "ru": "долл.",
        "en": "$"
      },
      "name": {
        "ru": "Доллар США",
        "en": "US Dollar"
      },
      "rate": 3.2,
      "is_default": False,
      "is_active": True
    }
]

# Константный список типов цен для инъекции
STATIC_PRICE_TYPES = [
    {
      "slug": "retail",
      "currency_code": "BYN",
      "is_default": True,
      "name": {
        "ru": "Цена продажи",
        "en": "Retail"
      },
      "description": {
        "ru": "Базовая розничная цена в системе",
        "en": "Base retail price in the system"
      }
    }
]

# Карта типов параметров для ваших атрибутов
ATTR_PARAM_TYPES = {
    "color": "string",
    "brand": "string",
    "set_sink": "string",
    "material": "string",
    "steel_thickness_sink": "numeric",
    "min_cab_width": "numeric",
    "effect_akril": "string",
    "texture": "string",
    "inclusions_akril": "string",
    "polishing_quartz": "string",
    "features_faucet": "string",
    "type_faucet": "string",
    "collection": "string",
}

def parse_float(text) -> float | int | None:
    """Безопасный парсинг чисел (дробных или целых) из любых строк"""
    if text is None:
        return None
    match = re.search(r'(\d+[\.,]\d+|\d+)', str(text))
    if match:
        num_str = match.group(1).replace(',', '.')
        return float(num_str) if '.' in num_str else int(num_str)
    return None

def is_valid_image(path) -> bool:
    """Проверяет, задан ли валидный путь к файлу изображения"""
    return path is not None and str(path).strip() != ""

def main():
    if not os.path.exists(INPUT_JSON):
        print(f"Error: Input JSON file not found: {INPUT_JSON}")
        sys.exit(1)

    print("Загрузка и трансформация данных для импорта...")
    with open(INPUT_JSON, 'r', encoding='utf-8') as f:
        data = json.load(f)

    # Принудительно внедряем константные валюты и типы цен
    print("Внедрение константных настроек валют и типов цен...")
    data["currencies"] = STATIC_CURRENCIES
    data["price_types"] = STATIC_PRICE_TYPES

    # 1. Трансформируем блок Атрибутов и их Опций
    for attr in data.get("attributes", []):
        code = attr.get("code")

        # Определяем тип параметров для опций этого атрибута
        param_type = ATTR_PARAM_TYPES.get(code)
        if param_type is None and attr.get("type") == "dictionary":
            param_type = "string" # Дефолт для обычных словарей

        attr["option_param_type"] = param_type

        for opt in attr.get("options", []):
            slug = opt.get("slug")
            meta = opt.get("meta") or {}

            # Рассчитываем значение param в зависимости от типа
            param_val = None
            if param_type == "numeric":
                param_val = parse_float(slug)
                if param_val is None:
                    param_val = parse_float(opt.get("value", {}).get("ru"))
            elif code == "color":
                param_val = meta.get("hex") or str(slug)
            else:
                param_val = str(slug) if slug is not None else None

            opt["param"] = param_val

    # 2. Трансформируем блок Продуктов (генерируем code на основе slug и проверяем фото)
    deactivated_count = 0
    for prod in data.get("products", []):
        if "code" not in prod:
            prod["code"] = prod.get("slug")

        # Проверяем изображения у самого товара
        prod_has_preview = is_valid_image(prod.get("preview_picture"))
        prod_has_detail = is_valid_image(prod.get("detail_picture"))

        # Проверяем изображения у всех вариаций этого товара
        variants_have_images = False
        for v in prod.get("variants", []):
            v_preview = is_valid_image(v.get("preview_picture"))
            v_detail = is_valid_image(v.get("detail_picture"))
            if v_preview or v_detail:
                variants_have_images = True
                break

        # Объединяем результаты проверки
        has_any_photo = prod_has_preview or prod_has_detail or variants_have_images

        # Если в исходном JSON активность уже была задана явно, сохраняем её, иначе по умолчанию True
        if "is_active" not in prod:
            prod["is_active"] = True

        # Если включена скрывающая опция и фото вообще нет, то деактивируем
        if HIDE_PRODUCTS_WITHOUT_IMAGES and not has_any_photo:
            prod["is_active"] = False
            deactivated_count += 1

    if HIDE_PRODUCTS_WITHOUT_IMAGES and deactivated_count > 0:
        print(f"  ⚠ Автоматически деактивировано товаров без фото: {deactivated_count}")

    # Сохраняем итоговый import_data.json
    print(f"Сохранение готового файла импорта -> {OUTPUT_JSON}")
    with open(OUTPUT_JSON, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=4)

if __name__ == "__main__":
    main()
