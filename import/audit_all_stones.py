import json
import csv
import re
import sys
import os
from typing import Any

INPUT_FILE = "import_data.json"
CSV_OUT = "stones_summary.csv"
JSON_ISSUES_OUT = "stones_issues.json"

STONE_TYPES = {"type_acrylic_stone", "type_quartz_stone"}

if not os.path.exists(INPUT_FILE):
    print(f"Файл {INPUT_FILE} не найден!")
    sys.exit(1)

with open(INPUT_FILE, "r", encoding="utf-8") as f:
    data = json.load(f, strict=False)

# 1. Построение карты справочников (slug/code -> русское название)
dictionaries = {}
for attr in data.get("attributes", []):
    attr_code = attr.get("code")
    mapping = {}
    for opt in attr.get("options", []):
        opt_val = opt.get("value", {})
        label = opt_val.get("ru") if isinstance(opt_val, dict) else str(opt_val)
        if opt.get("external_code"):
            mapping[opt["external_code"]] = label
        if opt.get("slug"):
            mapping[opt["slug"]] = label
        if opt.get("param"):
            mapping[str(opt["param"])] = label
    dictionaries[attr_code] = mapping

def resolve_label(attr_code: str, val: Any) -> str:
    if not val:
        return ""
    val_str = str(val).strip()
    return dictionaries.get(attr_code, {}).get(val_str, val_str)

products = data.get("products", [])

rows = []
issues_dict = {
    "missing_color": [],
    "corrupted_article": [],
    "missing_markup": [],
    "zero_markup": [],
    "polluted_variant_eav": []
}

for p in products:
    p_type = p.get("product_type_external_code")
    if p_type not in STONE_TYPES:
        continue

    p_code = p.get("code", "")
    p_name = p.get("name", {}).get("ru", "") if isinstance(p.get("name"), dict) else str(p.get("name") or "")
    p_eav = p.get("eav") or {}

    brand = resolve_label("brand", p_eav.get("brand"))
    collection = resolve_label("collection", p_eav.get("collection"))
    texture = resolve_label("texture", p_eav.get("texture"))

    variants = p.get("variants", [])
    for v in variants:
        v_sku = v.get("sku", "")
        v_eav = v.get("eav") or {}

        # Артикул, цвет, габариты
        supplier_art = str(v_eav.get("supplier_article") or "").strip()
        color = resolve_label("color", v_eav.get("color"))
        length = v_eav.get("length", "")
        width = v_eav.get("width", "")
        height = v_eav.get("height", "")

        cost_price = v.get("cost_price", "")
        currency = v.get("currency", "")
        markup = v.get("markup_percent")

        item_issues = []

        # Анализ проблем
        if not color or color == "—":
            item_issues.append("НЕТ_ЦВЕТА")
            issues_dict["missing_color"].append({
                "product_code": p_code,
                "name": p_name,
                "sku": v_sku
            })

        if any(w in supplier_art.lower() for w in ("серия", "группа", "collection")):
            item_issues.append("СБИТЫЙ_АРТИКУЛ")
            issues_dict["corrupted_article"].append({
                "product_code": p_code,
                "name": p_name,
                "sku": v_sku,
                "bad_article": supplier_art
            })

        if markup is None:
            item_issues.append("НЕТ_НАЦЕНКИ")
            issues_dict["missing_markup"].append({
                "product_code": p_code,
                "sku": v_sku,
                "cost_price": cost_price,
                "currency": currency
            })
        elif markup == 0:
            item_issues.append("НАЦЕНКА_0")
            issues_dict["zero_markup"].append({
                "product_code": p_code,
                "sku": v_sku
            })

        polluted = [k for k in ("brand", "collection", "texture") if k in v_eav]
        if polluted:
            item_issues.append(f"ДУБЛЬ_EAV({','.join(polluted)})")
            issues_dict["polluted_variant_eav"].append({
                "product_code": p_code,
                "sku": v_sku,
                "leaked_keys": polluted
            })

        rows.append({
            "Код товара": p_code,
            "Название": p_name,
            "Тип": "Акрил" if p_type == "type_acrylic_stone" else "Кварц",
            "Бренд": brand,
            "Коллекция": collection,
            "Текстура": texture,
            "SKU": v_sku,
            "Артикул поставщика": supplier_art,
            "Цвет": color,
            "Длина": length,
            "Ширина": width,
            "Толщина": height,
            "Себестоимость": cost_price,
            "Валюта": currency,
            "Наценка_%": "" if markup is None else markup,
            "Активен": "Да" if v.get("is_active", True) else "Нет",
            "По_умолчанию": "Да" if v.get("is_default", False) else "Нет",
            "Ошибки": " | ".join(item_issues)
        })

# Сохранение полной сводной таблицы в CSV (UTF-8 c BOM для правильного открытия в Excel)
fieldnames = [
    "Код товара", "Название", "Тип", "Бренд", "Коллекция", "Текстура",
    "SKU", "Артикул поставщика", "Цвет", "Длина", "Ширина", "Толщина",
    "Себестоимость", "Валюта", "Наценка_%", "Активен", "По_умолчанию", "Ошибки"
]

with open(CSV_OUT, "w", encoding="utf-8-sig", newline="") as f:
    writer = csv.DictWriter(f, fieldnames=fieldnames, delimiter=";")
    writer.writeheader()
    writer.writerows(rows)

# Сохранение только проблемных элементов в компактный JSON
with open(JSON_ISSUES_OUT, "w", encoding="utf-8") as f:
    json.dump(issues_dict, f, ensure_ascii=False, indent=2)

print("=" * 60)
print(f"Аудит завершен успешно!")
print(f"Всего камней просканировано: {len(rows)}")
print(f"Полный сводный файл: {CSV_OUT} ({round(os.path.getsize(CSV_OUT) / 1024, 1)} KB)")
print(f"Файл ошибок (JSON):   {JSON_ISSUES_OUT} ({round(os.path.getsize(JSON_ISSUES_OUT) / 1024, 1)} KB)")
print("=" * 60)
print("Сводка проблем:")
print(f" - Без цвета:                {len(issues_dict['missing_color'])}")
print(f" - Со сбитыми артикулами:    {len(issues_dict['corrupted_article'])}")
print(f" - Без поля markup_percent:  {len(issues_dict['missing_markup'])}")
print(f" - С наценкой 0%:            {len(issues_dict['zero_markup'])}")
print(f" - С замусоренным EAV:       {len(issues_dict['polluted_variant_eav'])}")
print("=" * 60)