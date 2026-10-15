import os, sys, json
from urllib.parse import unquote

if os.name == 'posix':
    BASE_DIR = "/home/maks-t/vms-box-platform-stone/import"
else:
    BASE_DIR = r"\\wsl.localhost\Ubuntu-24.04\home\maks-t\vms-box-platform-stone\import"

IMAGES_ROOT = os.path.join(BASE_DIR, "export_images")
DELETE = "--delete" in sys.argv

# Все JSON, которые могут ссылаться на картинки
JSON_FILES = ["import_data.json", "import_settings.json", "import_services.json"]


def collect_strings(node, out):
    if isinstance(node, dict):
        for v in node.values():
            collect_strings(v, out)
    elif isinstance(node, list):
        for v in node:
            collect_strings(v, out)
    elif isinstance(node, str):
        out.add(node.replace("\\", "/").lstrip("/"))


referenced = set()
for name in JSON_FILES:
    path = os.path.join(BASE_DIR, name)
    if not os.path.exists(path):
        print(f"[skip] {name} не найден")
        continue
    with open(path, "r", encoding="utf-8") as f:
        collect_strings(json.load(f), referenced)
    print(f"[ok] {name}")

used, unused = 0, []
for root, _, files in os.walk(IMAGES_ROOT):
    for fn in files:
        full = os.path.join(root, fn)
        rel = os.path.relpath(full, IMAGES_ROOT).replace("\\", "/")
        if rel in referenced or unquote(rel) in referenced:
            used += 1
        else:
            unused.append(full)

print(f"\nИспользуется: {used}, лишних: {len(unused)}")

if not DELETE:
    for p in unused[:30]:
        print("  ", p)
    if len(unused) > 30:
        print(f"   ... и ещё {len(unused) - 30}")
    print("\nЭто пробный запуск. Для удаления: py scripts/cleanup_images.py --delete")
    sys.exit(0)

for p in unused:
    os.remove(p)

# Удалить пустые папки
for root, dirs, files in os.walk(IMAGES_ROOT, topdown=False):
    if root != IMAGES_ROOT and not os.listdir(root):
        os.rmdir(root)

print("Удалено.")