import React from 'react';
import {H3} from '@/shared/components/ui/Typography';
import {EavAttribute} from '@/types/catalog';
import {AttributeValue} from './AttributeValue';

interface Props {
  attributes: Record<string, EavAttribute>;
}

export function ProductAttributes({attributes}: Props) {
  if (!attributes || Object.keys(attributes).length === 0) {
    return (
      <div className="flex-1">
        <H3 className="!text-muted-foreground !text-[13px] uppercase tracking-[0.15em] mb-6">
          Свойства (EAV)
        </H3>
        <p className="text-sm text-muted-foreground italic">Свойства не указаны</p>
      </div>
    );
  }

  return (
    <div className="flex-1">
      <H3 className="!text-muted-foreground !text-[13px] uppercase tracking-[0.15em] mb-6">
        Свойства (EAV)
      </H3>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-1">
        {Object.entries(attributes).map(([code, attr]) => (
          <div key={code} className="flex justify-between items-center py-3.5 border-b border-border/50 gap-4">
            <span className="text-[13px] text-muted-foreground font-medium">
              {attr.name}
            </span>
            <div className="text-[14px] text-right">
              <AttributeValue attribute={attr}/>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
