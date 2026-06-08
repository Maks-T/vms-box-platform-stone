import React from 'react';
import {FolderTree} from 'lucide-react';
import GlassPanel from '@/shared/components/ui/GlassPanel';
import Badge from '@/shared/components/ui/Badge';
import {BootstrapFamily} from '@/types/catalog';
import ProductTypeRow from "@/Pages/Bootstrap/components/BootstrapVisualizer/ProductTypeRow";


interface Props {
  families: BootstrapFamily[];
}

export function FamiliesTree({ families }: Props) {
  if (!families || families.length === 0) return null;

  return (
    <div>
      <h3 className="text-sm font-bold text-foreground uppercase tracking-widest mb-4 flex items-center gap-2">
        <FolderTree className="w-4 h-4 text-primary" />
        Структура каталога (Families & Types)
      </h3>
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {families.map((family) => (
          <GlassPanel key={family.code} variant="default" className="flex flex-col border-border bg-card shadow-sm">
            <div className="flex items-center justify-between border-b border-border pb-4 mb-4">
              <div className="flex flex-col">
                <span className="text-[10px] text-muted-foreground uppercase tracking-wider font-mono mb-1">{family.code}</span>
                <span className="text-lg font-bold text-foreground">{family.name}</span>
              </div>
              <Badge variant="blue" className="!px-2 !py-0.5 text-[10px]">Family</Badge>
            </div>

            <div className="flex flex-col gap-3">
              {family.types && family.types.map(type => (
                <ProductTypeRow key={type.code} type={type} />
              ))}
            </div>
          </GlassPanel>
        ))}
      </div>
    </div>
  );
}
