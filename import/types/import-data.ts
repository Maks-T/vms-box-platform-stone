/**
 * Локализованные строки (обычно ключи 'ru', 'en')
 */
export type TranslatableString = {
  ru?: string;
  en?: string;
  [locale: string]: string | undefined;
};

/**
 * Семейство товаров (Family)
 */
export interface ProductFamily {
  external_code: string; // fam_{code}
  code: string;
  name: TranslatableString;
}

/**
 * Тип товара (Product Type)
 */
export interface ProductType {
  external_code: string; // type_{code}
  family_external_code: string | null; // fam_{code}
  code: string;
  name: TranslatableString;
  payload?: Record<string, any> | null; // Различные системные настройки, step, minPart
}

/**
 * Категория (Category)
 */
export interface Category {
  external_code: string; // cat_{id}
  parent_external_code: string | null; // cat_{parent_id}
  slug: string;
  name: TranslatableString;
}

/**
 * Запись умного справочника (Complex Dictionary Record)
 */
export interface ComplexDictionaryRecord {
  external_code: string; // rec_{code}_{slug}
  slug: string;
  name: TranslatableString;
  payload: Record<string, any>; // base_cost, markup, rotate, cut и т.д.
}

/**
 * Умный справочник (Complex Dictionary)
 */
export interface ComplexDictionary {
  external_code: string; // dict_{code}
  code: string;
  name: TranslatableString;
  records: ComplexDictionaryRecord[];
}

/**
 * Мета-информация опции атрибута (цвет, иконка, фото)
 */
export interface OptionMeta {
  hex: string | null;
  image: string | null;
}

/**
 * Значение атрибута типа Dictionary (Option)
 */
export interface AttributeOption {
  external_code: string; // opt_{attr_code}_{slug}
  slug: string;
  value: TranslatableString;
  meta: OptionMeta;
}

/**
 * Атрибут (Характеристика)
 */
export interface Attribute {
  external_code: string; // attr_{code}
  code: string;
  type: 'string' | 'numeric' | 'boolean' | 'dictionary' | 'complex_reference' | string;
  name: TranslatableString;
  options?: AttributeOption[]; // Присутствует только если type === 'dictionary'
}

/**
 * Модификация товара (SKU / Variant)
 */
export interface ProductVariant {
  external_code: string; // sku_{id}
  sku: string;
  cost_price: number;
  stock: number;
  is_default: boolean;
  eav: Record<string, string | number | boolean>; // Ссылки на opt_, rec_ или простые значения
}

/**
 * Товар (Product)
 */
export interface Product {
  external_code: string; // prod_{id}
  product_type_external_code: string; // type_{code}
  category_external_code: string; // cat_{id}
  catalog_type: 'product' | 'service' | 'bundle';
  unit_code: string; // 'pcs', 'm', 'm2'
  slug: string;
  name: TranslatableString;
  eav: Record<string, string | number | boolean>;
  variants: ProductVariant[];
}

/**
 * Корневой объект структуры импорта (import_data.json)
 */
export interface ImportDataPayload {
  families: ProductFamily[];
  types: ProductType[];
  categories: Category[];
  complex_dictionaries: ComplexDictionary[];
  attributes: Attribute[];
  products: Product[];
}
