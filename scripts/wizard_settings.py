#!/usr/bin/env python3
"""
VMS-NC Box: Configuration Settings Wizard
Необходимые библиотеки: pip install questionary
Запуск: python wizard_settings.py
"""

import json
import sys
import os
import copy

try:
    import questionary
except ImportError:
    print("Ошибка: Не установлена библиотека 'questionary'.")
    sys.exit(1)

OUTPUT_FILE = "import_settings.json"

DEFAULT_SETTINGS = {
  "languages": ["ru", "en"],
  "channels": {
    "widget": {"is_public_default": True},
    "catalog": {"is_public_default": True},
    "b2b_portal": {"is_public_default": True}
  },
  "setting_schemas": {
    "attribute": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_filterable", "type": "boolean", "label": {"ru": "Использовать как фильтр", "en": "Use as filter"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_collapsed", "type": "boolean", "label": {"ru": "Свернуть по умолчанию", "en": "Collapsed by default"}, "width": 1, "is_system": True, "default": False},
      {"key": "filter_type", "type": "select", "label": {"ru": "Вид фильтра", "en": "Filter UI type"}, "width": 2, "is_system": True, "default": "checkbox", "options": {
          "checkbox": {"ru": "Список чекбоксов", "en": "Checkboxes"},
          "radio": {"ru": "Одиночный выбор", "en": "Radio buttons"},
          "color": {"ru": "Цветовые кружки", "en": "Color swatches"},
          "range": {"ru": "Диапазон (слайдер)", "en": "Range slider"}
      }}
    ],
    "complex_dictionary": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": True}
    ],
    "family": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": True},
      {"key": "show_in_menu", "type": "boolean", "label": {"ru": "Показывать в меню", "en": "Show in menu"}, "width": 2, "is_system": True, "default": True}
    ],
    "category": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": False},
      {"key": "show_in_menu", "type": "boolean", "label": {"ru": "Показывать в меню", "en": "Show in menu"}, "width": 1, "is_system": True, "default": True}
    ],
    "product_type": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": True}
    ],
    "product": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": False}
    ],
    "product_variant": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": False}
    ],
    "attribute_option": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": True}
    ],
    "price_type": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": False},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": False}
    ],
    "currency": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": False},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": False}
    ],
    "warehouse": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": True},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": False}
    ],
    "stock": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": False}
    ],
    "pipeline": [
      {"key": "is_public", "type": "boolean", "label": {"ru": "Опубликовано", "en": "Published"}, "width": 1, "is_system": True, "default": False},
      {"key": "is_settings_public", "type": "boolean", "label": {"ru": "Настройки публичны", "en": "Settings are public"}, "width": 1, "is_system": True, "default": False}
    ]
  }
}

class Colors:
    HEADER = '\033[95m'
    BLUE = '\033[94m'
    GREEN = '\033[92m'
    WARNING = '\033[93m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'

def check_exit(val):
    if val is None:
        print(f"\n{Colors.WARNING}Процесс прерван пользователем. Выход.{Colors.ENDC}")
        sys.exit(0)
    return val

class Wizard:
    def __init__(self):
        self.config = copy.deepcopy(DEFAULT_SETTINGS)

    def build_select_options(self, active_langs: list) -> dict:
        options = {}
        primary_lang = active_langs[0]
        print(f"\n{Colors.BLUE}Настройка опций для select{Colors.ENDC}")
        while True:
            val = check_exit(questionary.text("Value (системное значение):").ask())
            if not val.strip(): continue

            label_primary = check_exit(questionary.text(f"Label (на языке {primary_lang}):").ask())
            label_dict = {primary_lang: label_primary}
            for lang in active_langs[1:]: label_dict[lang] = label_primary

            options[val] = label_dict
            if not check_exit(questionary.confirm("Добавить еще опцию?").ask()): break
        return options

    def run(self):
        os.system('cls' if os.name == 'nt' else 'clear')
        print(f"{Colors.HEADER}{Colors.BOLD}VMS-NC Box: Configuration Settings Wizard{Colors.ENDC}")
        print("Нажмите Enter, чтобы принять значение по умолчанию.\n")

        langs_str = check_exit(questionary.text("Языки системы (через запятую):", default=", ".join(self.config["languages"])).ask())
        self.config["languages"] = [l.strip() for l in langs_str.split(",") if l.strip()]

        ch_str = check_exit(questionary.text("Каналы продаж (через запятую):", default=", ".join(self.config["channels"].keys())).ask())
        channels = [c.strip() for c in ch_str.split(",") if c.strip()]

        new_channels = {}
        for ch in channels:
            default_pub = self.config["channels"].get(ch, {}).get("is_public_default", True)
            is_pub = check_exit(questionary.confirm(f"Канал '{ch}': новые элементы публичны по умолчанию?", default=default_pub).ask())
            new_channels[ch] = {"is_public_default": is_pub}
        self.config["channels"] = new_channels

        print(f"\n{Colors.BOLD}{Colors.BLUE}Настройка схем (Полей){Colors.ENDC}")
        primary_lang = self.config["languages"][0]

        for entity, fields in list(self.config["setting_schemas"].items()):
            edit = check_exit(questionary.confirm(f"Сущность '{entity}' (полей: {len(fields)}). Настроить/добавить поля?", default=False).ask())
            if not edit: continue

            new_fields = []
            for f in fields:
                if f.get('is_system', False):
                    new_fields.append(f)
                    continue

                print(f"\nРедактирование: {f['key']}")
                k = check_exit(questionary.text("Ключ (очистите строку, чтобы удалить):", default=f['key']).ask())
                if not k.strip(): continue

                f['key'] = k.strip()
                f['type'] = check_exit(questionary.select("Тип:", choices=["text", "number", "boolean", "select"], default=f['type']).ask())

                lbl = f['label'].get(primary_lang, f['key']) if isinstance(f['label'], dict) else str(f['label'])
                new_lbl = check_exit(questionary.text(f"Название ({primary_lang}):", default=lbl).ask())

                if isinstance(f['label'], dict): f['label'][primary_lang] = new_lbl
                else: f['label'] = {primary_lang: new_lbl}

                f['width'] = int(check_exit(questionary.select("Ширина (1-4):", choices=["1","2","3","4"], default=str(f.get('width', 1))).ask()))
                if f['type'] == 'select' and 'options' not in f: f['options'] = self.build_select_options(self.config["languages"])
                new_fields.append(f)

            while True:
                print(f"\n{Colors.GREEN}Добавление нового поля в {entity}{Colors.ENDC}")
                k = check_exit(questionary.text("Ключ нового поля (пусто для завершения):").ask())
                if not k.strip(): break

                t = check_exit(questionary.select("Тип:", choices=["text", "number", "boolean", "select"]).ask())
                lbl = check_exit(questionary.text(f"Название ({primary_lang}):").ask())
                w = int(check_exit(questionary.select("Ширина (1-4):", choices=["1","2","3","4"], default="1").ask()))

                new_f = {"key": k.strip(), "type": t, "label": {primary_lang: lbl}, "width": w, "is_system": False}
                for lang in self.config["languages"][1:]: new_f["label"][lang] = k.strip()
                if t == 'select': new_f['options'] = self.build_select_options(self.config["languages"])
                new_fields.append(new_f)

            self.config["setting_schemas"][entity] = new_fields

        self._save()

    def _save(self):
        try:
            with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
                json.dump(self.config, f, ensure_ascii=False, indent=2)
            print(f"\n{Colors.GREEN}Настройки сохранены: {Colors.BOLD}{OUTPUT_FILE}{Colors.ENDC}")
        except Exception as e:
            print(f"{Colors.WARNING}Ошибка при сохранении: {e}{Colors.ENDC}")

if __name__ == "__main__":
    wizard = Wizard()
    wizard.run()
