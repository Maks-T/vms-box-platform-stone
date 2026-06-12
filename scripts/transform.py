#!/usr/bin/env python3
import json
import os
import re
import itertools
from typing import Any, Dict, Optional

# ==========================================
# 1. НАСТРОЙКИ ТРАНСФОРМАЦИИ
# ==========================================
DROP_LIST = {
    'min_sale_part_stone', 'min_product_size_stone', 'direction_division_stone',
    'is_pattern_repeat_stone', 'is_separate_cutting_stone', 'product_type',
    'type_stone', 'use_in_calc', 'price_category'
}

RENAME_RULES = {
    'bend_akril': 'is_bend',
    'price_categories': 'price_group',
    'color_ref': 'color',
    'collection_stone_ref': 'collection'
}

TYPE_OVERRIDES = {
    'is_bend': 'boolean',
    'price_group': 'complex_reference',
    'cutting_groups': 'complex_reference'
}

VARIANT_ONLY_ATTRS = {'color'}

DICT_SCHEMAS = {
    'price_group': [
        {"key": "cost_price", "type": "price", "label": {"ru": "Закупка (Материал) $", "en": "Cost (Material) $"}, "currency": "USD"}
    ],
    'cutting_groups': [
        {"key": "rotate", "type": "boolean", "label": {"ru": "Повтор рисунка", "en": "Pattern Repeat"}},
        {"key": "cut", "type": "boolean", "label": {"ru": "Раздельный раскрой", "en": "Separate Cutting"}}
    ],
    'thicknesses': [
        {"key": "material_code", "type": "text", "label": {"ru": "Системный код материала", "en": "Material Code"}},
        {"key": "thickness", "type": "number", "label": {"ru": "Толщина (мм)", "en": "Thickness (mm)"}},
        {"key": "coefficient", "type": "number", "label": {"ru": "Коэффициент наценки", "en": "Coefficient"}}
    ]
}

TYPE_ATTRIBUTES = {
    'acrylic_stone': ['color', 'price_group', 'cutting_groups', 'texture', 'collection', 'inclusions_akril', 'effect_akril', 'is_bend', 'length', 'width', 'height'],
    'quartz_stone': ['color', 'price_group', 'cutting_groups', 'texture', 'collection', 'polishing_quartz', 'length', 'width', 'height'],
    'kitchen_sink': ['color', 'brand', 'material', 'size_inner_sink', 'min_cab_width', 'steel_thickness_sink', 'set_sink'],
    'bathroom_sink': ['color', 'brand', 'material', 'size_inner_sink', 'set_sink'],
    'faucet': ['color', 'brand', 'features_faucet', 'type_faucet'],
    'dispenser': ['color', 'brand', 'type_faucet'],
    'edge': ['color'],
    'skirting': ['color']
}

def get_new_code(code: str) -> str: return RENAME_RULES.get(code, code)
def str_to_bool(val: Any) -> bool:
    if isinstance(val, bool): return val
    if isinstance(val, (int, float)): return bool(val)
    return str(val).lower() in ['да', 'yes', 'y', 'true', '1']
def clean_slug(slug: Any) -> str:
    if slug is None: return "default"
    return str(slug).replace('#', '').strip().lower()
def normalize_wsl_path(path_str: str) -> str:
    path_str = path_str.strip().strip("'").strip('"')
    if os.name == 'posix' and re.match(r'^[a-zA-Z]:[\\/]', path_str):
        drive = path_str[0].lower()
        tail = path_str[2:].replace('\\', '/')
        return f"/mnt/{drive}{tail}"
    return path_str
def load_source_data(default_path: str = 'source.json') -> Optional[Dict]:
    current_path = default_path
    while True:
        try:
            with open(current_path, 'r', encoding='utf-8') as f:
                print(f"✅ Файл '{current_path}' успешно загружен!")
                return json.load(f)
        except FileNotFoundError:
            user_input = input(f"❌ Файл '{current_path}' не найден. Введите путь (или 'q'): ")
            if user_input.strip().lower() in ['q']: return None
            current_path = normalize_wsl_path(user_input)
        except Exception as e:
            print(f"❌ Ошибка: {e}")
            return None

def process_eav(raw_eav: Dict[str, Any], attr_types: Dict[str, str]) -> Dict[str, Any]:
    clean_eav = {}
    for k, v in raw_eav.items():
        if k in DROP_LIST or v is None or v == "": continue
        new_k = get_new_code(k)
        a_type = TYPE_OVERRIDES.get(new_k) or attr_types.get(new_k)
        if a_type == 'dictionary':
            clean_eav[new_k] = f"opt_{new_k}_{clean_slug(v)}"
        elif a_type == 'complex_reference': clean_eav[new_k] = f"rec_{new_k}_{clean_slug(v)}"
        elif a_type == 'boolean': clean_eav[new_k] = str_to_bool(v)
        else: clean_eav[new_k] = v
    return clean_eav

# ==========================================
# 3. ОСНОВНАЯ ЛОГИКА ТРАНСФОРМАЦИИ
# ==========================================
def transform() -> None:
    print("🚀 Начинаем трансформацию данных...")
    src = load_source_data('source.json')
    if src is None: return

    dst = {"families": [], "types": [], "categories": [], "complex_dictionaries": [], "attributes": [], "products": []}
    attr_types: Dict[str, str] = {}

    print("-> Обработка семейств и типов...")
    for f in src.get('product_families', []):
        family_data = {"external_code": f"fam_{f['code']}", "code": f['code'], "name": f['name']}

        if f['code'] == 'stone':
            family_data['meta_schema'] = [
                {"key": "step", "type": "number", "label": {"ru": "Шаг размера", "en": "Size step"}, "width": 1},
                {"key": "minPart", "type": "number", "label": {"ru": "Минимальная часть", "en": "Min part"}, "width": 1},
                {"key": "maxStack", "type": "number", "label": {"ru": "Макс. стопка", "en": "Max stack"}, "width": 1},
                {"key": "axisX", "type": "boolean", "label": {"ru": "По оси X", "en": "Along X axis"}, "width": 1},

                {"key": "is_separate", "type": "boolean", "label": {"ru": "Кроить раздельно", "en": "Cut separately"}, "width": 2},
                {"key": "corner_add_length", "type": "number", "label": {"ru": "Добавка на внутр. угол (Длина мм)", "en": "Inner corner add (Length mm)"}, "width": 1},
                {"key": "corner_add_width", "type": "number", "label": {"ru": "Добавка на внутр. угол (Ширина мм)", "en": "Inner corner add (Width mm)"}, "width": 1}
            ]
        dst['families'].append(family_data)

    for t in src.get('product_types', []):
        payload = t.get('payload') or {}

        if t['code'] == 'quartz_stone':
            payload['is_separate'] = True
            payload['corner_add_length'] = 750
            payload['corner_add_width'] = 700
        elif t['code'] == 'acrylic_stone':
            payload['is_separate'] = False
            payload['corner_add_length'] = 920
            payload['corner_add_width'] = 760

        pricing_mode, pricing_attr_code, pricing_field = 'manual', None, None
        if t['code'] in ['acrylic_stone', 'quartz_stone']:
            pricing_mode, pricing_attr_code, pricing_field = 'complex_dictionary', 'price_group', 'cost_price'

        attached_attrs = [{"code": ac, "is_variant_only": ac in VARIANT_ONLY_ATTRS} for ac in TYPE_ATTRIBUTES.get(t['code'], [])]

        dst['types'].append({
            "external_code": f"type_{t['code']}", "family_external_code": f"fam_{t.get('family_code')}",
            "code": t['code'], "name": t['name'], "meta": payload, "attached_attributes": attached_attrs,
            "pricing_mode": pricing_mode, "pricing_attr_code": pricing_attr_code, "pricing_field": pricing_field
        })

    print("-> Обработка категорий...")
    dst['categories'].append({"external_code": "cat_accessory_root", "parent_external_code": None, "slug": "accessories", "name": {"ru": "Комплектующие и бортики", "en": "Accessories and edges"}})

    for c in src.get('categories', []):
        parent_ext = f"cat_{c['parent_id']}" if c.get('parent_id') else None
        if c['id'] in [21, 22]: parent_ext = "cat_accessory_root"
        dst['categories'].append({"external_code": f"cat_{c['id']}", "parent_external_code": parent_ext, "slug": c.get('slug') or str(c['id']), "name": c['name']})

    print("-> Обработка умных справочников...")
    for code, d in src.get('complex_dictionaries', {}).items():
        new_code = get_new_code(code)
        records = []
        if new_code == 'price_group':
            for r in d.get('records', []):
                rec_slug = str(r.get('slug') or r.get('id'))
                payload = r.get('payload') or {}
                old_cost = float(payload.get('cost_price', 0))
                records.append({
                    "external_code": f"rec_{new_code}_{clean_slug(rec_slug)}", "slug": rec_slug, "name": r.get('name'),
                    "meta": {"cost_price": round(old_cost * 0.7, 2), "cost_price_markup": 30.0}
                })
        elif new_code == 'cutting_groups':
            counter = 1
            for rotate, cut in itertools.product([True, False], repeat=2):
                records.append({
                    "external_code": f"rec_{new_code}_{counter}", "slug": str(counter),
                    "name": {"ru": f"Раскрой: {'Раздельный' if cut else 'Совместный'} | Шов: {'Разрешен' if rotate else 'Запрещен'}"},
                    "meta": {"rotate": rotate, "cut": cut}
                })
                counter += 1

        dst['complex_dictionaries'].append({
            "external_code": f"dict_{new_code}", "code": new_code, "name": d.get('name'),
            "meta_schema": DICT_SCHEMAS.get(new_code, []), "records": records
        })

    dst['complex_dictionaries'].append({
        "external_code": "dict_thicknesses", "code": "thicknesses", "name": {"ru": "Коэффициенты толщин", "en": "Thickness Coefficients"},
        "meta_schema": DICT_SCHEMAS['thicknesses'],
        "records": [
            {"external_code": "rec_thick_acr_12", "slug": "acr_12", "name": {"ru": "Акрил 12мм"}, "meta": {"material_code": "acrylic_stone", "thickness": 12, "coefficient": 1.0}},
            {"external_code": "rec_thick_acr_20", "slug": "acr_20", "name": {"ru": "Акрил 20мм"}, "meta": {"material_code": "acrylic_stone", "thickness": 20, "coefficient": 1.5}},
            {"external_code": "rec_thick_acr_30", "slug": "acr_30", "name": {"ru": "Акрил 30мм"}, "meta": {"material_code": "acrylic_stone", "thickness": 30, "coefficient": 2.0}},
            {"external_code": "rec_thick_qtz_15", "slug": "qtz_15", "name": {"ru": "Кварц 15мм"}, "meta": {"material_code": "quartz_stone", "thickness": 15, "coefficient": 1.0}},
            {"external_code": "rec_thick_qtz_20", "slug": "qtz_20", "name": {"ru": "Кварц 20мм"}, "meta": {"material_code": "quartz_stone", "thickness": 20, "coefficient": 1.3}}
        ]
    })

    print("-> Обработка атрибутов...")
    for a in src.get('attributes', []):
        old_code = a['code']
        if old_code in DROP_LIST: continue
        new_code = get_new_code(old_code)
        attr_type = TYPE_OVERRIDES.get(new_code, a.get('type'))
        attr_types[new_code] = attr_type
        options = []
        if attr_type == 'dictionary':
            for opt in src.get('dictionaries', {}).get(old_code, []):
                opt_slug = str(opt.get('slug', ''))
                payload = opt.get('payload') or {}

                hex_color = payload.get('hex') or payload.get('icon_hex') or (opt_slug if opt_slug.startswith('#') else None)

                meta = {
                    "hex": hex_color,
                    "image": payload.get('image')
                }

                options.append({
                    "external_code": f"opt_{new_code}_{clean_slug(opt_slug)}",
                    "slug": opt_slug,
                    "value": opt.get('value'),
                    "meta": meta
                })

        is_mult = True if new_code == 'marketing_tags' else False
        dst['attributes'].append({"external_code": f"attr_{new_code}", "code": new_code, "type": attr_type, "name": a['name'], "is_multiple": is_mult, "options": options})

    dst['attributes'].append({"external_code": "attr_price_group", "code": "price_group", "type": "complex_reference", "name": {"ru": "Ценовая категория", "en": "Price Group"}, "is_multiple": False, "options": []})
    attr_types['price_group'] = 'complex_reference'
    dst['attributes'].append({"external_code": "attr_cutting_groups", "code": "cutting_groups", "type": "complex_reference", "name": {"ru": "Группа раскроя", "en": "Cutting Group"}, "is_multiple": False, "options": []})
    attr_types['cutting_groups'] = 'complex_reference'
    dst['attributes'].append({"external_code": "attr_length", "code": "length", "type": "numeric", "name": {"ru": "Длина", "en": "Length"}, "is_multiple": False, "options": []})
    attr_types['length'] = 'numeric'
    dst['attributes'].append({"external_code": "attr_width", "code": "width", "type": "numeric", "name": {"ru": "Ширина", "en": "Width"}, "is_multiple": False, "options": []})
    attr_types['width'] = 'numeric'
    dst['attributes'].append({"external_code": "attr_height", "code": "height", "type": "numeric", "name": {"ru": "Толщина", "en": "Thickness"}, "is_multiple": False, "options": []})
    attr_types['height'] = 'numeric'

    dst['attributes'].append({"external_code": "attr_size_inner_sink", "code": "size_inner_sink", "type": "string", "name": {"ru": "Размер (внутренний)", "en": "Inner Size"}, "is_multiple": False, "options": []})
    attr_types['size_inner_sink'] = 'string'

    print("-> Обработка товаров и вариаций...")
    for item in src.get('items', []):
        raw_eav = item.get('eav') or {}
        if item.get('price_category_slug'): raw_eav['price_group'] = item['price_category_slug']
        if item.get('cutting_group_id'): raw_eav['cutting_groups'] = str(item['cutting_group_id'])

        product_raw_eav = {k: v for k, v in raw_eav.items() if get_new_code(k) not in VARIANT_ONLY_ATTRS}
        variant_raw_eav = {k: v for k, v in raw_eav.items() if get_new_code(k) in VARIANT_ONLY_ATTRS}

        variants = []
        src_variants = item.get('variants', [])

        if not src_variants:
            variants.append({
                "external_code": f"sku_{item['id']}_def", "sku": f"{item.get('slug', 'item')}-def",
                "price": float(item.get('price', 0)), "cost_price": float(item.get('price', 0)) * 0.8,
                "stock": 10.0, "is_default": True, "preview_picture": item.get('preview_picture'), "detail_picture": item.get('detail_picture'),
                "eav": process_eav(variant_raw_eav, attr_types)
            })
        else:
            for index, v in enumerate(src_variants):
                v_eav = {**variant_raw_eav, **(v.get('eav') or {})}
                is_default = bool(v.get('is_default')) if 'is_default' in v else (index == 0)

                variants.append({
                    "external_code": f"sku_{v['id']}", "sku": v.get('slug'),
                    "price": float(v.get('price', 0)), "cost_price": float(v.get('price', 0)) * 0.8,
                    "stock": 10.0, "is_default": is_default,
                    "preview_picture": v.get('preview_picture'), "detail_picture": v.get('detail_picture'),
                    "eav": process_eav(v_eav, attr_types)
                })

        pt_code = item.get('product_type_code', 'acrylic_stone')
        if pt_code == "item":
            type_stone = item.get('eav', {}).get('type_stone')
            if type_stone == 'quartz':
                pt_code = 'quartz_stone'
            else:
                pt_code = 'acrylic_stone'

        unit_code = "m" if pt_code in ['edge', 'skirting'] else "pcs"

        dst['products'].append({
            "external_code": f"prod_{item['id']}", "product_type_external_code": f"type_{pt_code}", "category_external_code": f"cat_{item['category_id']}",
            "catalog_type": "product", "unit_code": unit_code, "slug": item['slug'], "name": item['name'],
            "preview_picture": item.get('preview_picture'), "detail_picture": item.get('detail_picture'),
            "eav": process_eav(product_raw_eav, attr_types), "variants": variants
        })

    output_filename = 'import_data.json'
    print(f"-> Сохранение в {output_filename}...")
    with open(output_filename, 'w', encoding='utf-8') as f:
        json.dump(dst, f, ensure_ascii=False, indent=2)
    print(f"✨ Готово! Файл '{output_filename}' успешно сгенерирован.")

if __name__ == "__main__":
    try:
        transform()
    except KeyboardInterrupt:
        print("\n🛑 Процесс прерван пользователем.")
