/** 1. Метаданные (HEX, Картинки, Схемы, Наценки) */
export interface EavMeta {
  hex?: string | null;
  image?: string | null;
  icon?: string | null;

  [key: string]: any;
}

/** 2. Значение справочника (Цвет, Бренд, Группа раскроя) */
export interface EavValueOption {
  slug: string;
  name: string;
  meta: EavMeta;
}

/** 3. Структура EAV-атрибута (с оберткой value) */
export interface EavAttribute {
  name: string;
  type: 'string' | 'numeric' | 'boolean' | 'dictionary' | 'complex_reference';
  is_multiple: boolean;
  value: string | number | boolean | EavValueOption | Array<EavValueOption | string | number | boolean> | null;
}

/** 4. Единица измерения */
export interface Unit {
  slug: string;
  name: string;
  symbol: string;
}

/** 5. Модификация товара (SKU) */
export interface ProductVariant {
  id: number;
  external_code: string | null;
  sku: string;
  slug: string | null;
  name: string;
  prices: Record<string, number>;
  stock: number;
  is_default: boolean;
  preview_picture: string | null;
  detail_picture: string | null;
  unit?: Unit | null;
  attributes: Record<string, EavAttribute>;
  settings: Record<string, any> | null;
}

/** 6. Главный Товар (Камень, Мойка, Услуга) */
export interface StoneProduct {
  id: number;
  external_code: string | null;
  name: string;
  slug: string;
  price_from: number;
  preview_picture: string | null;
  detail_picture: string | null;
  unit: Unit | null;
  attributes: Record<string, EavAttribute>;
  settings: Record<string, any> | null;
  variants: ProductVariant[];
}

/** 7. Фильтры (Каталог) */
export interface FilterSettings {
  filter_type: 'checkbox' | 'select' | 'color' | 'range';
  is_collapsed: boolean;

  [key: string]: any;
}

export interface Filter {
  code: string;
  name: string;
  type: string;
  settings: FilterSettings;
  options: EavValueOption[];
}

/** 8. Конфигурация (Bootstrap) */
export interface BootstrapProductType {
  code: string;
  name: string;
  meta: Record<string, any> | null;
}

export interface BootstrapFamily {
  code: string;
  name: string;
  schema: { key: string; type: string; label: string }[] | null;
  types: BootstrapProductType[];
}

export interface BootstrapConfig {
  base_currency: {
    code: string;
    symbol: string;
  };
  dictionaries: any[];
  families: BootstrapFamily[];
}

/** 9. Услуга (для матрицы цен) */
export interface ServiceMatrixItem {
  id: number;
  external_code: string | null;
  slug: string;
  name: string;
  preview_picture: string | null;
  detail_picture: string | null;
  unit: Unit | null;
  attributes: Record<string, EavAttribute>;
  settings: Record<string, any> | null;
  prices: Record<string, number>;
}
