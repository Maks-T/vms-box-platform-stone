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


def download_and_process_image(url: str, cache: dict) -> str:
    parsed_url = urllib.parse.urlparse(url)
    raw_filename = os.path.basename(parsed_url.path)

    if not raw_filename:
        raw_filename = "downloaded_image.jpg"

    filename = clean_filename(raw_filename)
    local_path = os.path.join(IMAGES_DIR, filename)
    new_json_path = JSON_PATH_PREFIX + filename

    if url in cache and os.path.exists(local_path):
        return cache[url]

    print(f"Downloading: {url} -> {filename}")
    temp_path = os.path.join(IMAGES_DIR, "temp_download.tmp")
    try:
        req = urllib.request.Request(
            url,
            headers={'User-Agent': 'Mozilla/5.0'}
        )
        with urllib.request.urlopen(req) as response, open(temp_path, 'wb') as out_file:
            out_file.write(response.read())

        crop_to_square_and_resize(temp_path, local_path, target_size=528)

        if os.path.exists(temp_path):
            os.remove(temp_path)

        cache[url] = new_json_path
        return new_json_path

    except Exception as e:
        print(f"Error downloading {url}: {e}")
        if os.path.exists(temp_path):
            os.remove(temp_path)
        return url


def recursive_json_search(node, cache: dict):
    if isinstance(node, dict):
        for k, v in node.items():
            node[k] = recursive_json_search(v, cache)
        return node
    elif isinstance(node, list):
        return [recursive_json_search(item, cache) for item in node]
    elif isinstance(node, str) and (node.startswith("http://") or node.startswith("https://")):
        return download_and_process_image(node, cache)
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

    processed_data = recursive_json_search(data, url_cache)

    try:
        with open(CACHE_FILE, 'w', encoding='utf-8') as cf:
            json.dump(url_cache, cf, ensure_ascii=False, indent=4)
    except Exception as e:
        print(f"Failed to save cache file: {e}")

    with open(OUTPUT_JSON, 'w', encoding='utf-8') as f:
        json.dump(processed_data, f, ensure_ascii=False, indent=4)


if __name__ == "__main__":
    main()
