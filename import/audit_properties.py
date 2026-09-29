import json

with open("import_data.json", "r", encoding="utf-8") as f:
    data = json.load(f, strict=False)

products = data.get("products", [])
stone_types = {"type_acrylic_stone", "type_quartz_stone"}

missing_dimensions = []
polluted_variant_eav = []
suspicious_articles = []
missing_colors = []
missing_markup = []

for p in products:
    p_code = p.get("code")
    p_type = p.get("product_type_external_code")

    if p_type not in stone_types:
        continue

    for v in p.get("variants", []):
        sku = v.get("sku")
        eav = v.get("eav", {})

        # 1. Проверка габаритов
        if not (eav.get("length") and eav.get("width") and eav.get("height")):
            missing_dimensions.append((p_code, sku))

        # 2. Проверка дублирования общих свойств в варианте
        if any(k in eav for k in ("brand", "collection", "texture")):
            polluted_variant_eav.append(sku)

        # 3. Проверка цвета
        if not eav.get("color"):
            missing_colors.append((p_code, sku))

        # 4. Проверка сбитых артикулов (содержат слова "серия", "группа")
        art = str(eav.get("supplier_article") or "")
        if any(w in art.lower() for w in ("серия", "группа", "collection")):
            suspicious_articles.append((p_code, sku, art))

        # 5. Проверка наценки
        if "markup_percent" not in v:
            missing_markup.append(sku)

print("=" * 50)
print(f"Всего камней: {len([p for p in products if p.get('product_type_external_code') in stone_types])}")
print(f"Камни без габаритов (length/width/height): {len(missing_dimensions)}")
print(f"Камни без указания цвета (color): {len(missing_colors)} -> {missing_colors}")
print(f"Варианты с замусоренным EAV (brand/collection в SKU): {len(polluted_variant_eav)}")
print(f"Варианты с битыми артикулами поставщика: {len(suspicious_articles)}")
print(f"Варианты без поля markup_percent: {len(missing_markup)}")
print("=" * 50)

if suspicious_articles:
    print("\nПримеры сбитых артикулов:")
    for item in suspicious_articles[:5]:
        print(f"  Товар: {item[0]} | SKU: {item[1]} -> Артикул: '{item[2]}'")