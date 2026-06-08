import React from 'react';
import { BootstrapConfig } from '@/types/catalog';
import { StatsRow } from './StatsRow';
import { FamiliesTree } from './FamiliesTree';
import { DictionariesList } from './DictionariesList';

interface Props {
  config: BootstrapConfig;
}

export function BootstrapVisualizer({ config }: Props) {
  if (!config) return null;

  return (
    <div className="flex flex-col gap-10 mb-12">
      <StatsRow config={config} />
      <FamiliesTree families={config.families || []} />
      <DictionariesList dictionaries={config.dictionaries || []} />
    </div>
  );
}
