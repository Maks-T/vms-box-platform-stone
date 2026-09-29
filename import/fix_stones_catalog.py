import json
import re
import shutil
import sys
import os

INPUT_FILE = "import_data.json"
BACKUP_FILE = "import_data.pre_fix.backup.json"

STONE_TYPES = {"type_acrylic_stone", "type_quartz_stone"}
PRODUCT_LEVEL_EAV = {"brand", "collection", "texture"}

# Словарь исправления артикулов Kerrock
KERROCK_ARTICLES = {
    "108_snow_white": "108",
    "100_arctic_white": "100",
    "101_polar_white": "101",
    "109_white_acrylic": "109",
    "117_dusty_white": "117",
    "118_pearl_white": "118"
}


def fix_balanced_parentheses(text: str) -> str:
    """Закрывает незакрытые круглые скобки в названии, если они были обрезаны."""
    open_count = text.count("(")
    close_count = text.count(")")
    if open_count > close_count:
        text = text + (")" * (open_count - close_count))
    return text


def main():
    if not os.path.exists(INPUT_FILE):
        print(f"Файл {INPUT_FILE} не найден!")
        sys.exit(1)

    print(f"Создание резервной копии: {BACKUP_FILE}")
    shutil.copyfile(INPUT_FILE, BACKUP_FILE)

    print(f"Чтение {INPUT_FILE}...")
    with open(INPUT_FILE, "r", encoding="utf-8") as f:
        data = json.load(f, strict=False)

    products = data.get("products", [])

    fixed_colors = 0
    fixed_articles = 0
    cleaned_eav_count = 0
    filled_markups = 0
    fixed_names_count = 0

    for p in products:
        p_type = p.get("product_type_external_code")
        if p_type not in STONE_TYPES:
            continue

        p_code = p.get("code", "")

        # 1. Исправление оборванных скобок в имени
        if isinstance(p.get("name"), dict):
            ru_name = p["name"].get("ru", "")
            fixed_name = fix_balanced_parentheses(ru_name)
            if fixed_name != ru_name:
                p["name"]["ru"] = fixed_name
                fixed_names_count += 1

        for v in p.get("variants", []):
            v_eav = v.setdefault("eav", {})

            # 2. Исправление цвета ON095 Onyx
            if p_code == "on095_onyx" or v.get("sku") == "on095_onyx_tselyy_list_3680kh760kh12mm":
                if not v_eav.get("color"):
                    v_eav["color"] = "opt_color_black"
                    fixed_colors += 1

            # 3. Восстановление артикулов Kerrock
            if p_code in KERROCK_ARTICLES:
                v_eav["supplier_article"] = KERROCK_ARTICLES[p_code]
                fixed_articles += 1

            # 4. Удаление дублирующих атрибутов товара из EAV варианта
            for leaked_key in PRODUCT_LEVEL_EAV:
                if leaked_key in v_eav:
                    del v_eav[leaked_key]
                    cleaned_eav_count += 1

            # 5. Проставление markup_percent, если поле отсутствует
            if "markup_percent" not in v or v["markup_percent"] is None:
                # Стандартная наценка 20%
                v["markup_percent"] = 20
                filled_markups += 1

            # Если наценка 0% и это НЕ супер-премиум Cambria с готовой розничной ценой
            elif v.get("markup_percent") == 0:
                p_eav = p.get("eav", {})
                brand_code = str(p_eav.get("brand") or "").lower()
                if "cambria" not in brand_code:
                    # Для обычных камней выставляем рабочую наценку 20%
                    v["markup_percent"] = 20
                    filled_markups += 1

    print("Запись исправленных данных...")
    with open(INPUT_FILE, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

    print("=" * 60)
    print("Отчет об исправлении свойств:")
    print(f" - Исправлено цветов:                {fixed_colors}")
    print(f" - Восстановлено артикулов Kerrock:  {fixed_articles}")
    print(f" - Вычищено дубликатов из вариантов: {cleaned_eav_count}")
    print(f" - Проставлено наценок (20%):        {filled_markups}")
    print(f" - Восстановлено скобок в названиях: {fixed_names_count}")
    print("=" * 60)


if __name__ == "__main__":
    main()