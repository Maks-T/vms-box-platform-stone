import React from 'react';
import {Coins, FolderTree, BookOpen} from 'lucide-react';
import GlassPanel from '@/shared/components/ui/GlassPanel';
import {IconBox} from '@/shared/components/ui/IconBox';
import {BootstrapConfig} from '@/types/catalog';

interface Props {
  config: BootstrapConfig;
}

export function StatsRow({config}: Props) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
      <GlassPanel variant="light" padding="sm" className="flex items-center gap-4">
        <IconBox variant="primary" shape="circle">
          <Coins className="w-5 h-5"/>
        </IconBox>
        <div>
          <div className="text-[11px] text-muted-foreground uppercase tracking-widest font-bold mb-0.5">Базовая валюта
          </div>
          <div
            className="text-lg font-black text-foreground">{config.base_currency?.code} ({config.base_currency?.symbol})
          </div>
        </div>
      </GlassPanel>

      <GlassPanel variant="light" padding="sm" className="flex items-center gap-4">
        <IconBox variant="light" shape="circle" className="text-emerald-500 bg-emerald-50 border-emerald-100">
          <FolderTree className="w-5 h-5"/>
        </IconBox>
        <div>
          <div className="text-[11px] text-muted-foreground uppercase tracking-widest font-bold mb-0.5">Дерево
            каталога
          </div>
          <div className="text-lg font-black text-foreground">{config.families?.length || 0} Семейств</div>
        </div>
      </GlassPanel>

      <GlassPanel variant="light" padding="sm" className="flex items-center gap-4">
        <IconBox variant="light" shape="circle" className="text-amber-500 bg-amber-50 border-amber-100">
          <BookOpen className="w-5 h-5"/>
        </IconBox>
        <div>
          <div className="text-[11px] text-muted-foreground uppercase tracking-widest font-bold mb-0.5">Умные
            справочники
          </div>
          <div className="text-lg font-black text-foreground">{config.dictionaries?.length || 0} Матриц</div>
        </div>
      </GlassPanel>
    </div>
  );
}
