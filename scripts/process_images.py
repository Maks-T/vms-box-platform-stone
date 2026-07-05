import os
import json
import re
import urllib.request
import urllib.parse
import sys

try:
    from PIL import Image
except ImportError:
    print("Error: Pillow library is not installed. Run: pip install Pillow")
    sys.exit(1)

if os.name == 'posix':
    BASE_DIR = "/home/maks-t/vms-box-platform-stone/import"
else:
    BASE_DIR = r"\\wsl.localhost\Ubuntu-24.04\home\maks-t\vms-box-platform-stone\import"

INPUT_JSON = os.path.join(BASE_DIR, "import_mebel_raw_ru.json")
OUTPUT_JSON = os.path.join(BASE_DIR, "import_mebel_raw_ru_pic.json")
CACHE_FILE = os.path.join(BASE_DIR, "image_cache.json")
IMAGES_DIR = os.path.join(BASE_DIR, "export_images", "products")
JSON_PATH_PREFIX = "products/"


def clean_filename(filename: str) -> str:
    name, _ = os.path.splitext(filename)
    cleaned = re.sub(r'-sl\d+x\d+px?', '', name, flags=re.IGNORECASE)
    cleaned = re.sub(r'-+', '-', cleaned)
    return cleaned + ".webp"


def crop_to_square_and_resize(source_path: str, dest_path: str, target_size: int = 528):
    """Обрезка изображения до квадрата и изменение размера (для камня)"""
    try:
        with Image.open(source_path) as img:
            width, height = img.size
            min_side = min(width, height)

            left = (width - min_side) / 2
            top = (height - min_side) / 2
            right = (width + min_side) / 2
            bottom = (height + min_side) / 2

            img_cropped = img.crop((left, top, right, bottom))
            img_resized = img_cropped.resize((target_size, target_size), Image.Resampling.LANCZOS)
            img_resized.save(dest_path, "WEBP", quality=85)
    except Exception as e:
        print(f"Error processing image from {source_path} to {dest_path}: {e}")


def resize_image_preserve_aspect(source_path: str, dest_path: str, target_max_size: int = 528):
    """Пропорциональное сжатие по максимальной стороне без обрезки (для моек, смесителей и др.)"""
    try:
        with Image.open(source_path) as img:
            width, height = img.size

            # Рассчитываем новые размеры с сохранением соотношения сторон
            if width > height:
                new_width = target_max_size
                new_height = max(1, int(height * (target_max_size / width)))
            else:
                new_height = target_max_size
                new_width = max(1, int(width * (target_max_size / height)))

            img_resized = img.resize((new_width, new_height), Image.Resampling.LANCZOS)
            img_resized.save(dest_path, "WEBP", quality=85)
    except Exception as e:
        print(f"Error resizing image from {source_path} to {dest_path}: {e}")


def download_and_process_image(url: str, cache: dict, is_stone: bool = False) -> str:
    parsed_url = urllib.parse.urlparse(url)
    raw_filename = os.path.basename(parsed_url.path)

    if not raw_filename:
        raw_filename = "downloaded_image.jpg"

    filename = clean_filename(raw_filename)
    local_path = os.path.join(IMAGES_DIR, filename)
    new_json_path = JSON_PATH_PREFIX + filename

    if url in cache and os.path.exists(local_path):
        return cache[url]

    print(f"Downloading: {url} -> {filename} (Is Stone: {is_stone})")
    temp_path = os.path.join(IMAGES_DIR, "temp_download.tmp")
    try:
        req = urllib.request.Request(
            url,
            headers={'User-Agent': 'Mozilla/5.0'}
        )
        with urllib.request.urlopen(req) as response, open(temp_path, 'wb') as out_file:
            out_file.write(response.read())

        # Выбираем алгоритм обработки в зависимости от типа товара
        if is_stone:
            crop_to_square_and_resize(temp_path, local_path, target_size=528)
        else:
            resize_image_preserve_aspect(temp_path, local_path, target_max_size=528)

        if os.path.exists(temp_path):
            os.remove(temp_path)

        cache[url] = new_json_path
        return new_json_path

    except Exception as e:
        print(f"Error downloading {url}: {e}")
        if os.path.exists(temp_path):
            os.remove(temp_path)
        return url


def process_json_data(node, cache: dict, is_stone: bool = False):
    """
    Контекстный обход JSON.
    Передает флаг is_stone вниз по дереву, если обнаруживает тип товара со словом 'stone'.
    """
    if isinstance(node, dict):
        current_is_stone = is_stone
        # Если в словаре есть тип продукта, проверяем, камень ли это
        if "product_type_external_code" in node:
            type_code = str(node["product_type_external_code"]).lower()
            current_is_stone = "stone" in type_code

        new_dict = {}
        for k, v in node.items():
            # Модификации (variants) автоматически унаследуют статус parent-товара
            new_dict[k] = process_json_data(v, cache, is_stone=current_is_stone)
        return new_dict

    elif isinstance(node, list):
        return [process_json_data(item, cache, is_stone=is_stone) for item in node]

    elif isinstance(node, str) and (node.startswith("http://") or node.startswith("https://")):
        return download_and_process_image(node, cache, is_stone=is_stone)

    return node


def main():
    os.makedirs(IMAGES_DIR, exist_ok=True)

    url_cache = {}
    if os.path.exists(CACHE_FILE):
        try:
            with open(CACHE_FILE, 'r', encoding='utf-8') as cf:
                url_cache = json.load(cf)
        except Exception as e:
            print(f"Failed to read cache file: {e}")

    if not os.path.exists(INPUT_JSON):
        print(f"Error: Input JSON file not found: {INPUT_JSON}")
        sys.exit(1)

    with open(INPUT_JSON, 'r', encoding='utf-8') as f:
        data = json.load(f)

    # Запускаем контекстную обработку данных с дефолтным флагом is_stone=False
    processed_data = process_json_data(data, url_cache, is_stone=False)

    try:
        with open(CACHE_FILE, 'w', encoding='utf-8') as cf:
            json.dump(url_cache, cf, ensure_ascii=False, indent=4)
    except Exception as e:
        print(f"Failed to save cache file: {e}")

    with open(OUTPUT_JSON, 'w', encoding='utf-8') as f:
        json.dump(processed_data, f, ensure_ascii=False, indent=4)


if __name__ == "__main__":
    main()
