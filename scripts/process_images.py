import argparse
import hashlib
import json
import os
import re
import sys
import urllib.parse
import urllib.request

try:
    from PIL import Image
except ImportError:
    print("Error: Pillow library is not installed. Run: pip install Pillow")
    sys.exit(1)

if os.name == "posix":
    BASE_DIR = "/home/maks-t/vms-box-platform-stone/import"
else:
    BASE_DIR = r"\\wsl.localhost\Ubuntu-24.04\home\maks-t\vms-box-platform-stone\import"

MERGED_JSON = os.path.join(BASE_DIR, "import_data_merged.json")
DEFAULT_INPUT_JSON = (
    MERGED_JSON if os.path.exists(MERGED_JSON) else os.path.join(BASE_DIR, "import_data.json")
)
DEFAULT_OUTPUT_JSON = os.path.join(BASE_DIR, "import_data.json")
CACHE_FILE = os.path.join(BASE_DIR, "image_cache.json")
IMAGES_DIR = os.path.join(BASE_DIR, "export_images", "products")
JSON_PATH_PREFIX = "products/"

# Скачиваем изображения только из ключей картинок, чтобы не задеть source_url
IMAGE_KEYS = {"preview_picture", "detail_picture", "image"}


def resolve_cross_os_path(path_str: str) -> str:
    """Автоматически приводит путь к формату текущей ОС (Windows <-> WSL)."""
    path_str = path_str.strip().strip("'").strip('"')
    if os.name == "posix" and re.match(r"^[a-zA-Z]:[\\/]", path_str):
        drive = path_str[0].lower()
        tail = path_str[2:].replace("\\", "/")
        return f"/mnt/{drive}{tail}"
    return os.path.abspath(path_str)


def clean_filename(filename: str, url: str) -> str:
    name, _ = os.path.splitext(filename)
    name = urllib.parse.unquote(name)
    cleaned = re.sub(r"-sl\d+x\d+px?", "", name, flags=re.IGNORECASE)
    cleaned = re.sub(r"[^a-zA-Zа-яА-Я0-9_-]+", "-", cleaned)
    cleaned = re.sub(r"-+", "-", cleaned).strip("-")
    short_hash = hashlib.md5(url.encode("utf-8")).hexdigest()[:6]
    base = cleaned or "stone"
    return f"{base}-{short_hash}.webp"


def crop_to_square_and_resize(source_path: str, dest_path: str, target_size: int = 528) -> bool:
    try:
        with Image.open(source_path) as img:
            if img.mode not in ("RGB", "RGBA"):
                img = img.convert("RGB")
            width, height = img.size
            min_side = min(width, height)

            left = (width - min_side) / 2
            top = (height - min_side) / 2
            right = (width + min_side) / 2
            bottom = (height + min_side) / 2

            img_cropped = img.crop((left, top, right, bottom))
            img_resized = img_cropped.resize((target_size, target_size), Image.Resampling.LANCZOS)
            img_resized.save(dest_path, "WEBP", quality=85)
        return True
    except Exception as e:
        print(f"Error processing image from {source_path} to {dest_path}: {e}")
        return False


def download_and_process_image(url: str, cache: dict, stats: dict) -> str:
    if url in cache:
        cached_rel = cache[url]
        cached_filename = os.path.basename(cached_rel)
        if os.path.exists(os.path.join(IMAGES_DIR, cached_filename)):
            stats["cached"] += 1
            return cached_rel

    parsed_url = urllib.parse.urlparse(url)
    raw_filename = os.path.basename(parsed_url.path) or "downloaded_image.jpg"

    filename = clean_filename(raw_filename, url)
    local_path = os.path.join(IMAGES_DIR, filename)
    new_json_path = JSON_PATH_PREFIX + filename

    if os.path.exists(local_path):
        cache[url] = new_json_path
        stats["cached"] += 1
        return new_json_path

    print(f"Downloading: {url} -> {new_json_path}")
    temp_path = os.path.join(IMAGES_DIR, f"temp_{os.getpid()}.tmp")
    try:
        req = urllib.request.Request(
            url,
            headers={
                "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
            },
        )
        with urllib.request.urlopen(req, timeout=25) as response, open(temp_path, "wb") as out_file:
            out_file.write(response.read())

        ok = crop_to_square_and_resize(temp_path, local_path, target_size=528)

        if os.path.exists(temp_path):
            os.remove(temp_path)

        if ok:
            cache[url] = new_json_path
            stats["downloaded"] += 1
            return new_json_path

        stats["errors"] += 1
        return url

    except Exception as e:
        print(f"Error downloading {url}: {e}")
        if os.path.exists(temp_path):
            os.remove(temp_path)
        stats["errors"] += 1
        return url


def recursive_json_search(node, cache: dict, stats: dict, current_key: str | None = None):
    if isinstance(node, dict):
        for k, v in node.items():
            node[k] = recursive_json_search(v, cache, stats, current_key=k)
        return node
    elif isinstance(node, list):
        return [recursive_json_search(item, cache, stats, current_key=current_key) for item in node]
    elif (
        isinstance(node, str)
        and current_key in IMAGE_KEYS
        and (node.startswith("http://") or node.startswith("https://"))
    ):
        return download_and_process_image(node, cache, stats)
    return node


def main():
    parser = argparse.ArgumentParser(
        description="Скачивание внешних изображений из JSON, обрезка в квадрат 528x528 WebP и замена путей."
    )
    parser.add_argument(
        "-i", "--input",
        default=DEFAULT_INPUT_JSON,
        help="Путь к входному JSON файлу (например: D:\\sand\\PARSERS\\masterstone\\import_data_merged.json)",
    )
    parser.add_argument(
        "-o", "--output",
        default=DEFAULT_OUTPUT_JSON,
        help="Путь к выходному JSON файлу (по умолчанию: import/import_data.json)",
    )
    args = parser.parse_args()

    input_json = resolve_cross_os_path(args.input)
    output_json = resolve_cross_os_path(args.output)

    os.makedirs(IMAGES_DIR, exist_ok=True)

    print("=" * 60)
    print(f"Входной JSON:  {input_json}")
    print(f"Выходной JSON: {output_json}")
    print(f"Папка фото:    {IMAGES_DIR}")
    print("=" * 60)

    url_cache = {}
    if os.path.exists(CACHE_FILE):
        try:
            with open(CACHE_FILE, "r", encoding="utf-8") as cf:
                url_cache = json.load(cf)
        except Exception as e:
            print(f"Failed to read cache file: {e}")

    if not os.path.exists(input_json):
        print(f"Error: Input JSON file not found: {input_json}")
        sys.exit(1)

    with open(input_json, "r", encoding="utf-8") as f:
        data = json.load(f)

    stats = {"downloaded": 0, "cached": 0, "errors": 0}
    try:
        processed_data = recursive_json_search(data, url_cache, stats)
    except KeyboardInterrupt:
        print("\n[!] Прервано пользователем, сохраняем прогресс...")
        processed_data = data

    try:
        with open(CACHE_FILE, "w", encoding="utf-8") as cf:
            json.dump(url_cache, cf, ensure_ascii=False, indent=4)
    except Exception as e:
        print(f"Failed to save cache file: {e}")

    os.makedirs(os.path.dirname(output_json), exist_ok=True)
    with open(output_json, "w", encoding="utf-8") as f:
        json.dump(processed_data, f, ensure_ascii=False, indent=4)

    print("-" * 60)
    print(f"Скачано новых картинок: {stats['downloaded']}")
    print(f"Взято из кэша:          {stats['cached']}")
    print(f"Ошибок:                 {stats['errors']}")
    print(f"Готовый файл сохранён:  {output_json}")


if __name__ == "__main__":
    main()