import React from 'react';
import { H3 } from '@/shared/components/ui/Typography';
import { EavAttribute } from '@/types/catalog';
import { AttributeValue } from './AttributeValue';

interface Props {
  attributes: Record<string, EavAttribute>;
}

export function ProductAttributes({ attributes }: Props) {
  if (!attributes || Object.keys(attributes).length === 0) {
    return null;
  }

  const filteredAttributes = Object.entries(attributes).filter(
    ([code]) => code !== 'brand'
  );

  if (filteredAttributes.length === 0) {
    return null;
  }

  return (
    <div className="flex-1 mt-4">
      <H3 className="!text-zinc-400 !text-[11px] uppercase tracking-widest font-bold mb-3">
        Свойства (EAV)
      </H3>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-1">
        {filteredAttributes.map(([code, attr]) => (
          <div key={code} className="flex justify-between items-center py-2.5 border-b border-zinc-100 gap-4">
            <span className="text-xs text-zinc-500 font-medium">
              {attr.name}
            </span>
            <div className="text-xs font-semibold text-zinc-900 text-right">
              <AttributeValue attribute={attr}/>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}