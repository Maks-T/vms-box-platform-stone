import React from 'react';
import {EavAttribute, EavValueOption} from '@/types/catalog';
import {ValueMultiple} from './ValueMultiple';
import {ValueSingleOption} from './ValueSingleOption';

interface Props {
  attribute: EavAttribute;
}

export function AttributeValue({attribute}: Props) {
  const val = attribute.value;

  
  if (val === null || val === undefined || val === '') {
    return <span className="text-muted-foreground">—</span>;
  }

  // Множественный выбор (Теги)
  if (attribute.is_multiple && Array.isArray(val)) {
    return <ValueMultiple values={val}/>;
  }

  // Одиночный объект из словаря (Цвет, Раскрой)
  if (typeof val === 'object' && !Array.isArray(val) && val !== null && 'name' in val) {
    return <ValueSingleOption option={val as EavValueOption}/>;
  }

  // Булево значение (Да / Нет)
  if (typeof val === 'boolean') {
    return <span className="font-semibold text-foreground">{val ? 'Да' : 'Нет'}</span>;
  }

  // Обычная строка или число (Размеры, Текст)
  return <span className="font-semibold text-foreground">{String(val)}</span>;
}
