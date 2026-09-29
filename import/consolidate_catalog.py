#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import sys
import json
import re
import shutil
from typing import Dict, Tuple, List, Any

INPUT_FILE = "import_data.json"
BACKUP_FILE = "import_data.backup.json"

TARGET_STONE_TYPES = {
    "type_acrylic_stone",
    "type_quartz_stone"
}

VARIANT_ONLY_ATTRS = {"length", "width", "height"}


def update_types_schema(types_list: List[Dict[str, Any]]) -> int:
    """
    Обновляет схему attached_attributes для типов камня:
    устанавливает is_variant_only = True для length, width, height.
    """
    updated_count = 0
    for t in types_list:
        if t.get("external_code") in TARGET_STONE_TYPES or t.get("code") in TARGET_STONE_TYPES:
            for attr in t.get("attached_attributes", []):
                if attr.get("code") in VARIANT_ONLY_ATTRS:
                    if not attr.get("is_variant_only"):
                        attr["is_variant_only"] = True
                        updated_count += 1
    return updated_count


def normalize_stone_name(raw_name: str) -> str:
    """
    Очищает название камня от габаритов, долей слэбов, толщин и маркетинговых приписок.
    """
    if not raw_name:
        return ""

    s = raw_name

    # 1. Удаление маркетинговых приписок в конце строки
    s = re.sub(
        r'[\s\-–—]+(акция|распродажа|сток\s*цена|сток|снят[а-я\s]*с\s*производства|снят[а-я\s]*|новинка\s*\d{4}|новинка).*$',
        '',
        s,
        flags=re.IGNORECASE
    )

    # 2. Удаление габаритов вида: 3200x1600, 3200х1600, 3680x760x12, 3500х2000 мм и т.д.
    s = re.sub(
        r'\(?\b\d{3,4}\s*[*xхXХ×]\s*\d{3,4}(\s*[*xхXХ×]\s*\d{1,2})?\s*(?:мм|mm|м\.п\.|м)?\.?\)?',
        '',
        s,
        flags=re.IGNORECASE
    )

    # 3. Удаление толщины вида: 6 мм, 12 мм, 20 мм, 30 мм, 12mm, 20mm
    s = re.sub(r'\(?\b\d{1,2}\s*(?:мм|mm)\b\.?\)?', '', s, flags=re.IGNORECASE)

    # 4. Удаление указаний формата и долей слэба: (целый слэб), 1/2 слэба, jumbo, лист и т.д.
    s = re.sub(
        r'\(?\b(?:целый|1/2|1/4|3/4|более\s*\d+[\.,]?\d*)\s*(?:слэб[а-я]*|слеб[а-я]*|лист[а-я]*|jumbo)\b\.?\)?',
        '',
        s,
        flags=re.IGNORECASE
    )
    s = re.sub(r'\(?\b(?:jumbo|слэб[а-я]*|слеб[а-я]*|лист[а-я]*)\b\.?\)?', '', s, flags=re.IGNORECASE)

    # 5. Удаление оставшихся одиночных хвостов
    s = re.sub(r'\(?\bh\d{1,2}\b\.?\)?', '', s, flags=re.IGNORECASE)

    # 6. Очистка кавычек и нормализация пробелов
    s = re.sub(r'["«»„“”\']', '', s)
    s = re.sub(r'\s+', ' ', s)

    return s.strip(" -–—.,_*()")


def get_variant_priority(variant: Dict[str, Any], product_type: str) -> Tuple[int, float, float]:
    """
    Вычисляет приоритет варианта для сортировки:
    1. Полноразмерный стандартный формат (Jumbo 3200x1600 для кварца, 3680x760 для акрила).
    2. Максимальная площадь слэба (length * width).
    3. Длина слэба.
    """
    eav = variant.get("eav") or {}
    try:
        length = float(eav.get("length") or 0)
    except (ValueError, TypeError):
        length = 0.0

    try:
        width = float(eav.get("width") or 0)
    except (ValueError, TypeError):
        width = 0.0

    is_preferred_jumbo = 0
    if product_type == "type_quartz_stone":
        if length >= 3200 and width >= 1600:
            is_preferred_jumbo = 1
    elif product_type == "type_acrylic_stone":
        if length >= 3680 and width >= 760:
            is_preferred_jumbo = 1

    area = length * width
    return (is_preferred_jumbo, area, length)


def ensure_variant_eav_dimensions(variant: Dict[str, Any], fallback_eav: Dict[str, Any]) -> None:
    """
    Гарантирует наличие числовых полей length, width, height в EAV варианта.
    Если они отсутствуют в варианте, пробует подтянуть их из EAV товара.
    """
    var_eav = variant.setdefault("eav", {})

    for dim in ("length", "width", "height"):
        val = var_eav.get(dim)
        if val is None or val == "":
            val = fallback_eav.get(dim)

        if val is not None:
            try:
                var_eav[dim] = float(val) if "." in str(val) else int(val)
            except (ValueError, TypeError):
                var_eav[dim] = 0
        else:
            var_eav[dim] = 0


def main():
    target_path = INPUT_FILE
    if not os.path.exists(target_path):
        alt_path = os.path.join("import", INPUT_FILE)
        if os.path.exists(alt_path):
            target_path = alt_path
        else:
            print(f"Error: file '{INPUT_FILE}' not found.")
            sys.exit(1)

    backup_path = target_path.replace(".json", ".backup.json")
    print(f"Creating backup: {backup_path}")
    shutil.copyfile(target_path, backup_path)

    print(f"Loading data from: {target_path}")
    with open(target_path, "r", encoding="utf-8") as f:
        data = json.load(f, strict=False)

    # 1. Обновление types
    types_list = data.get("types", [])
    updated_attrs_count = update_types_schema(types_list)
    print(f"Updated 'is_variant_only' flags in types: {updated_attrs_count}")

    # 2. Разделение каталога
    products = data.get("products", [])
    initial_product_count = len(products)

    non_stone_products: List[Dict[str, Any]] = []
    stone_groups: Dict[Tuple[str, str, str], Dict[str, Any]] = {}

    for prod in products:
        ptype = prod.get("product_type_external_code")

        # Сантехнику, мойки и аксессуары не трогаем вообще
        if ptype not in TARGET_STONE_TYPES:
            non_stone_products.append(prod)
            continue

        raw_name = ""
        if isinstance(prod.get("name"), dict):
            raw_name = prod["name"].get("ru", "")
        elif isinstance(prod.get("name"), str):
            raw_name = prod["name"]

        norm_name = normalize_stone_name(raw_name)
        prod_eav = prod.get("eav") or {}
        brand = str(prod_eav.get("brand") or "").strip().lower()

        group_key = (ptype, brand, norm_name.lower())

        # Подготовка вариантов текущего товара
        current_variants = prod.get("variants", [])
        for v in current_variants:
            ensure_variant_eav_dimensions(v, prod_eav)

        if group_key not in stone_groups:
            master_product = dict(prod)
            # Очищаем название основного товара
            if isinstance(master_product.get("name"), dict):
                master_product["name"]["ru"] = norm_name
            else:
                master_product["name"] = {"ru": norm_name}

            # Удаляем размерные характеристики из EAV самого товара (они теперь variant-only)
            master_eav = master_product.setdefault("eav", {})
            for dim in VARIANT_ONLY_ATTRS:
                master_eav.pop(dim, None)

            stone_groups[group_key] = {
                "product": master_product,
                "variants_map": {}  # deduplication by sku / external_code
            }

        # Сбор вариантов в общую группу без дублей
        group_var_map = stone_groups[group_key]["variants_map"]
        for v in current_variants:
            v_key = v.get("sku") or v.get("external_code")
            if v_key:
                if v_key not in group_var_map:
                    group_var_map[v_key] = v
            else:
                group_var_map[id(v)] = v

    # 3. Сортировка вариантов, выставление активности и финализация товаров
    consolidated_stones: List[Dict[str, Any]] = []
    merged_multi_variant_count = 0

    for (ptype, _, _), group_data in stone_groups.items():
        product = group_data["product"]
        variants = list(group_data["variants_map"].values())

        # Сортировка: сначала Jumbo, затем по убыванию площади
        variants.sort(key=lambda v: get_variant_priority(v, ptype), reverse=True)

        if variants:
            # 1-й вариант: приоритетный, активный по умолчанию
            variants[0]["is_default"] = True
            variants[0]["is_active"] = True

            # Остальные варианты: сохраняются в БД, но скрываются из публичной выдачи
            for v in variants[1:]:
                v["is_default"] = False
                v["is_active"] = False

        product["variants"] = variants
        consolidated_stones.append(product)

        if len(variants) > 1:
            merged_multi_variant_count += 1

    # Объединяем обратно
    data["products"] = non_stone_products + consolidated_stones
    final_product_count = len(data["products"])

    print(f"Writing updated catalog to: {target_path}")
    with open(target_path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

    print("--- Execution Summary ---")
    print(f"Total initial products: {initial_product_count}")
    print(f"Total consolidated products: {final_product_count}")
    print(f"Non-stone products untouched: {len(non_stone_products)}")
    print(f"Consolidated stone products: {len(consolidated_stones)}")
    print(f"Stones with multiple merged variants: {merged_multi_variant_count}")


if __name__ == "__main__":
    main()