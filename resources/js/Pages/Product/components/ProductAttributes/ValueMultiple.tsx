import React from 'react';
import Badge from '@/shared/components/ui/Badge';
import {EavValueOption} from '@/types/catalog';

interface Props {
  values: Array<EavValueOption | string | number | boolean>;
}

export function ValueMultiple({values}: Props) {
  return (
    <div className="flex flex-wrap gap-2 justify-end">
      {values.map((v, idx) => {

        const isOption = typeof v === 'object' && v !== null && 'name' in v;
        const option = isOption ? (v as EavValueOption) : null;

        return (
          <Badge key={idx} variant="gray" className="!px-2.5 !py-1 text-xs font-semibold shadow-sm">
            {option?.meta?.hex && (
              <div className="w-2.5 h-2.5 rounded-full border border-border mr-1.5"
                   style={{backgroundColor: option.meta.hex}}/>
            )}
            {option?.meta?.image && (
              <img src={option.meta.image} alt="" className="w-3.5 h-3.5 rounded-full object-cover mr-1.5"/>
            )}
            {isOption ? option.name : String(v)}
          </Badge>
        );
      })}
    </div>
  );
}
