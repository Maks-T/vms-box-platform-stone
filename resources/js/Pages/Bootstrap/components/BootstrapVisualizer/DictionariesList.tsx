import React from 'react';
import {BookOpen, Settings2} from 'lucide-react';
import GlassPanel from '@/shared/components/ui/GlassPanel';
import Badge from '@/shared/components/ui/Badge';

interface Props {
  dictionaries: any[];
}

export function DictionariesList({dictionaries}: Props) {
  if (!dictionaries || dictionaries.length === 0) return null;

  return (
    <div>
      <h3 className="text-sm font-bold text-foreground uppercase tracking-widest mb-4 flex items-center gap-2">
        <BookOpen className="w-4 h-4 text-amber-500"/>
        Умные справочники
      </h3>
      <div className="flex flex-wrap gap-4">
        {dictionaries.map(dict => (
          <GlassPanel key={dict.code} variant="default" padding="sm"
                      className="bg-card border-border shadow-sm min-w-[250px] flex-1 lg:flex-none">
            <div className="flex items-center gap-3 mb-2">
              <Settings2 className="w-4 h-4 text-muted-foreground"/>
              <span className="font-bold text-foreground text-sm">{dict.name}</span>
            </div>
            <div
              className="text-[12px] text-muted-foreground flex justify-between items-center border-t border-border pt-2 mt-2">
              <span className="font-mono">{dict.code}</span>
              <Badge variant="gray" className="!text-[10px] !py-0 !px-1.5">{dict.records?.length || 0} записей</Badge>
            </div>
          </GlassPanel>
        ))}
      </div>
    </div>
  );
}
