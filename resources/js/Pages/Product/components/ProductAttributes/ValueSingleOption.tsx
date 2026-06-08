import React from 'react';
import {EavValueOption} from '@/types/catalog';

interface Props {
  option: EavValueOption;
}

export function ValueSingleOption({option}: Props) {
  return (
    <div className="flex items-center gap-2">
      {option.meta?.hex && (
        <div className="w-4 h-4 rounded-full border border-border shadow-sm shrink-0"
             style={{backgroundColor: option.meta.hex}}/>
      )}
      {option.meta?.image && (
        <img src={option.meta.image} alt=""
             className="w-5 h-5 rounded-full object-cover border border-border shadow-sm shrink-0"/>
      )}
      <span className="font-semibold text-foreground">{option.name}</span>
    </div>
  );
}
