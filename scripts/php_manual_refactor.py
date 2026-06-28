import os
import re
import json
import hashlib
import shutil
import questionary
from rich.console import Console
from rich.syntax import Syntax
from rich.panel import Panel
from rich.theme import Theme
from rich.text import Text

# --- КОНФИГУРАЦИЯ ---
CACHE_FILE = os.path.join(os.path.dirname(__file__), ".refactor_cache.json")
SETTINGS_FILE = os.path.join(os.path.dirname(__file__), ".refactor_settings.json")

custom_theme = Theme({
    "info": "dim cyan",
    "warning": "magenta",
    "danger": "bold red",
    "success": "bold green",
    "comment": "bold yellow",
    "hotkey": "bold white on blue",
    "footer": "white on #333333",
})
console = Console(theme=custom_theme)

# Глобальный стек истории
undo_stack = []

# ==========================================
# 1. СИСТЕМНЫЕ ФУНКЦИИ И ТРАНСФОРМАЦИИ
# ==========================================

def get_file_hash(filepath):
    hasher = hashlib.sha256()
    try:
        with open(filepath, 'rb') as f:
            for chunk in iter(lambda: f.read(4096), b""): hasher.update(chunk)
        return hasher.hexdigest()
    except: return ""

def load_json(path):
    return json.load(open(path, 'r', encoding='utf-8')) if os.path.exists(path) else {}

def save_json(path, data):
    json.dump(data, open(path, 'w', encoding='utf-8'), indent=2, ensure_ascii=False)

def strip_numbers(text):
    """Удаляет цифры в начале строки (например, '1.', '1)', '1. '), сохраняя отступы."""
    match = re.match(r"^(\s*)\d+[\s\.\)-]+(.*)$", text)
    if match:
        spaces, remaining = match.groups()
        return spaces + remaining
    return text

def capitalize_text(text):
    """Делает первую букву текста заглавной, остальные строчными."""
    first_letter_idx = -1
    for idx, char in enumerate(text):
        if char.isalpha():
            first_letter_idx = idx
            break
    if first_letter_idx != -1:
        return (text[:first_letter_idx] +
                text[first_letter_idx].upper() +
                text[first_letter_idx+1:].lower())
    return text.lower()

def modify_comment_text(comment_str, transform_func):
    """
    Применяет трансформацию к тексту внутри комментария,
    не затрагивая синтаксические маркеры (//, /*, *, */).
    """
    if comment_str.startswith("//"):
        content = comment_str[2:]
        return "//" + transform_func(content)
    elif comment_str.startswith("#"):
        content = comment_str[1:]
        return "#" + transform_func(content)
    elif comment_str.startswith("/*"):
        is_docblock = comment_str.startswith("/**")
        prefix = "/**" if is_docblock else "/*"
        suffix = "*/"

        inner = comment_str[len(prefix):-len(suffix)]
        lines = inner.splitlines(keepends=True)
        new_lines = []

        for line in lines:
            # Находим декоративные элементы (пробелы и звездочки в начале строки)
            match = re.match(r"^(\s*\*?\s*)(.*)$", line, re.DOTALL)
            if match:
                decorations, text_content = match.groups()
                # Извлекаем символы переноса строки в конце текста
                line_end_match = re.search(r"(\r?\n)$", text_content)
                if line_end_match:
                    le = line_end_match.group(1)
                    actual_text = text_content[:-len(le)]
                    new_lines.append(decorations + transform_func(actual_text) + le)
                else:
                    new_lines.append(decorations + transform_func(text_content))
            else:
                new_lines.append(transform_func(line))
        return prefix + "".join(new_lines) + suffix
    return transform_func(comment_str)

# ==========================================
# 2. ИНТЕРФЕЙС
# ==========================================

def display_header_and_code(content, start_idx, filepath, i, total, current_comment):
    console.clear()
    ext = os.path.splitext(filepath)[1]
    lang = {".php": "php", ".js": "javascript", ".ts": "typescript"}.get(ext, "php")
    lines = content.splitlines(keepends=True)

    char_acc, line_idx = 0, 0
    for idx, line in enumerate(lines):
        if char_acc + len(line) > start_idx:
            line_idx = idx
            break
        char_acc += len(line)

    margin = 5
    start_line, end_line = max(0, line_idx - margin), min(len(lines), line_idx + margin + 1)
    snippet = "".join(lines[start_line:end_line])

    syntax = Syntax(snippet, lang, theme="monokai", line_numbers=True,
                    start_line=start_line + 1, highlight_lines={line_idx + 1})

    console.print(Panel(syntax, title=f" {os.path.basename(filepath)} ({i+1}/{total}) ", border_style="cyan"))
    console.print(f"\n [bold yellow]>>>[/bold yellow] [bold white]{current_comment.strip()}[/bold white]\n")

def print_sticky_footer(has_history):
    term_width, term_height = shutil.get_terminal_size()
    footer_text = Text("", style="footer")

    keys = [(" 1|K ", "Keep"), (" 2|D ", "Del"), (" 3|E ", "Edit")]
    if has_history: keys.append((" 4|U ", "Undo"))
    keys.extend([(" 5|S ", "Skip"), (" 6|Q ", "Quit")])

    for k, d in keys:
        footer_text.append(k, style="hotkey")
        footer_text.append(f" {d}  ", style="footer")

    current_len = len(footer_text)
    if current_len < term_width:
        footer_text.append(" " * (term_width - current_len), style="footer")

    console.print(footer_text, end="", highlight=False)

# ==========================================
# 3. ОСНОВНОЙ ПРОЦЕСС
# ==========================================

def process_file(filepath, cache, all_files, start_at_index=0):
    abs_p = os.path.abspath(filepath)
    with open(filepath, 'r', encoding='utf-8') as f:
        original_content = f.read()

    pattern = re.compile(r'(//.*?$|/\*.*?\*/|/\*\*.*?\*/)', re.MULTILINE | re.DOTALL)
    matches = list(pattern.finditer(original_content))

    if not matches:
        cache[abs_p] = get_file_hash(filepath)
        return "next"

    new_content, offset = original_content, 0
    c_idx = start_at_index

    while c_idx < len(matches):
        match = matches[c_idx]
        start, end = match.span()

        display_header_and_code(new_content, start + offset, filepath, c_idx, len(matches), match.group(0))

        # ФОРМИРУЕМ МЕНЮ
        choices = [
            questionary.Choice("1. Оставить (Keep)", value="keep", shortcut_key="1"),
            questionary.Choice("2. Удалить (Delete)", value="delete", shortcut_key="2"),
            questionary.Choice("3. Редактировать (Edit)", value="edit", shortcut_key="3"),
        ]

        if undo_stack:
            choices.append(questionary.Choice("4. Назад (Undo)", value="undo", shortcut_key="4"))

        choices.extend([
            questionary.Choice("5. Пропустить файл (Skip)", value="skip", shortcut_key="5"),
            questionary.Choice("6. Выход (Quit)", value="quit", shortcut_key="6"),
        ])

        action = questionary.select(
            "Выберите действие:",
            choices=choices,
            style=questionary.Style([
                ('highlighted', 'fg:#00ffff bold'),
                ('pointer', 'fg:#00ffff bold'),
            ]),
            use_shortcuts=True
        ).ask()

        if action == "undo":
            prev = undo_stack.pop()
            if prev['file_idx'] != all_files.index(filepath):
                return ("prev", prev)
            else:
                new_content, offset, c_idx = prev['content'], prev['offset'], prev['comment_idx']
                continue

        if action == "skip": return "next"
        if action == "quit":
            save_json(CACHE_FILE, cache)
            exit()

        if not action: continue

        # Сохраняем в историю ПЕРЕД изменением
        undo_stack.append({
            'filepath': filepath, 'file_idx': all_files.index(filepath),
            'content': new_content, 'comment_idx': c_idx, 'offset': offset
        })

        replacement = match.group(0)
        if action == "delete":
            replacement = ""

        elif action == "edit":
            current_text = match.group(0)
            editing = True

            while editing:
                console.print("\n")
                console.print(Panel(current_text, title="Текущий вид комментария", border_style="yellow"))

                edit_action = questionary.select(
                    "Выберите операцию или примените макрос:",
                    choices=[
                        questionary.Choice("1. ✍️ Ввести вручную", value="manual", shortcut_key="1"),
                        questionary.Choice("2. 🔢 Убрать цифры в начале", value="strip_numbers", shortcut_key="2"),
                        questionary.Choice("3. abc Сделать строчными (lowercase)", value="lowercase", shortcut_key="3"),
                        questionary.Choice("4. Abc Только первая заглавная", value="capitalize", shortcut_key="4"),
                        questionary.Choice("5. ABC Сделать прописными (uppercase)", value="uppercase", shortcut_key="5"),
                        questionary.Choice("6. 💾 Применить и сохранить (Save)", value="save", shortcut_key="6"),
                        questionary.Choice("7. ❌ Отменить изменения (Cancel)", value="cancel", shortcut_key="7")
                    ],
                    style=questionary.Style([
                        ('highlighted', 'fg:#00ffff bold'),
                        ('pointer', 'fg:#00ffff bold'),
                    ]),
                    use_shortcuts=True
                ).ask()

                if not edit_action or edit_action == "cancel":
                    replacement = None
                    editing = False
                elif edit_action == "manual":
                    manual_text = questionary.text("Новый текст комментария:", default=current_text).ask()
                    if manual_text is not None:
                        current_text = manual_text
                elif edit_action == "strip_numbers":
                    current_text = modify_comment_text(current_text, strip_numbers)
                elif edit_action == "lowercase":
                    current_text = modify_comment_text(current_text, lambda x: x.lower())
                elif edit_action == "capitalize":
                    current_text = modify_comment_text(current_text, capitalize_text)
                elif edit_action == "uppercase":
                    current_text = modify_comment_text(current_text, lambda x: x.upper())
                elif edit_action == "save":
                    replacement = current_text
                    editing = False

            if replacement is None:
                # Откат истории, так как действие редактирования было отменено
                undo_stack.pop()
                continue

        new_content = new_content[:start + offset] + replacement + new_content[end + offset:]
        offset += len(replacement) - len(match.group(0))
        c_idx += 1

    # Сохранение по окончанию файла
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
    cache[abs_p] = get_file_hash(filepath)
    return "next"

def main():
    global all_files
    settings = load_json(SETTINGS_FILE) or {"extensions": [".php", ".js", ".ts", ".jsx", ".tsx"]}
    cache = load_json(CACHE_FILE)

    console.clear()
    path = questionary.path("Укажите путь к проекту:").ask()
    if not path: return

    all_files = []
    for root, _, filenames in os.walk(path):
        if any(x in root for x in ['vendor', '.git', 'storage', 'node_modules', 'dist']): continue
        for f in filenames:
            if any(f.endswith(ext) for ext in settings["extensions"]):
                all_files.append(os.path.join(root, f))

    f_idx = 0
    while f_idx < len(all_files):
        fpath = all_files[f_idx]
        if cache.get(os.path.abspath(fpath)) == get_file_hash(fpath) and (not undo_stack or undo_stack[-1]['file_idx'] < f_idx):
            f_idx += 1
            continue

        result = process_file(fpath, cache, all_files)

        if isinstance(result, tuple) and result[0] == "prev":
            prev_data = result[1]
            with open(prev_data['filepath'], 'w', encoding='utf-8') as f:
                f.write(prev_data['content'])
            f_idx = prev_data['file_idx']
            process_file(fpath, cache, all_files, start_at_index=prev_data['comment_idx'])
            continue

        f_idx += 1
        save_json(CACHE_FILE, cache)

    console.print("\n[success]✨ Готово! Все файлы обработаны.[/success]")

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        console.print("\n[warning]Прервано.[/warning]")
