# scripts/transform_import_data.py
import os
import json
import re
import sys

# Определяем пути к файлам
BASE_DIR = "/home/maks-t/vms-box-platform-stone/import" if os.name == 'posix' else r"\\wsl.localhost\Ubuntu-24.04\home\maks-t\vms-box-platform-stone\import"
INPUT_JSON = os.path.join(BASE_DIR, "import_mebel_raw_ru_pic.json")
OUTPUT_JSON = os.path.join(BASE_DIR, "import_data.json")

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

def main():
    if not os.path.exists(INPUT_JSON):
        print(f"Error: Input JSON file not found: {INPUT_JSON}")
        sys.exit(1)

    print("Загрузка и трансформация данных для импорта...")
    with open(INPUT_JSON, 'r', encoding='utf-8') as f:
        data = json.load(f)

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
                # Вытаскиваем число из слага (например, "0,9-mm" -> 0.9) или из значения
                param_val = parse_float(slug)
                if param_val is None:
                    param_val = parse_float(opt.get("value", {}).get("ru"))
            elif code == "color":
                # Для цвета параметром по умолчанию будет hex-код
                param_val = meta.get("hex") or str(slug)
            else:
                # Для остальных строк параметром будет сам слаг
                param_val = str(slug) if slug is not None else null

            opt["param"] = param_val

    # Сохраняем итоговый import_data.json для Laravel импортера
    print(f"Сохранение готового файла импорта -> {OUTPUT_JSON}")
    with open(OUTPUT_JSON, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=4)

if __name__ == "__main__":
    main()
