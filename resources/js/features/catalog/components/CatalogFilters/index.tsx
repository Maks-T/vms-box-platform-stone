import React from 'react';
import { CheckboxFilter } from './CheckboxFilter';
import { ColorFilter } from './ColorFilter';
import { Filter } from '@/types/catalog';

interface Props {
  filters: Filter[];
  activeFilters: Record<string, string[]>;
  onToggle: (code: string, slug: string) => void;
}

export const CatalogFilters = ({ filters, activeFilters, onToggle }: Props) => {
  const displayableFilters = filters.filter((f) => f.options && f.options.length > 0);

  if (displayableFilters.length === 0) {
    return (
      <div className="space-y-4 py-2">
        <div className="h-3 bg-zinc-100 rounded-sm w-2/3 animate-pulse" />
        <div className="h-6 bg-zinc-100 rounded-sm w-full animate-pulse" />
        <div className="h-6 bg-zinc-100 rounded-sm w-full animate-pulse" />
      </div>
    );
  }

  return (
    <div className="w-full space-y-5">
      {displayableFilters.map((filter) => {
        const filterType = filter.settings?.filter_type || 'checkbox';
        const activeValues = activeFilters[filter.code] || [];
        const toggleHandler = (slug: string) => onToggle(filter.code, slug);

        return (
          <div key={filter.code} className="flex flex-col pt-3 first:pt-0 border-t first:border-0 border-border">
            <h3 className="text-[10px] font-bold tracking-widest text-zinc-400 uppercase mb-3 select-none">
              {filter.name}
            </h3>

            {filterType === 'color' ? (
              <ColorFilter
                options={filter.options}
                activeValues={activeValues}
                onToggle={toggleHandler}
              />
            ) : (
              <CheckboxFilter
                options={filter.options}
                activeValues={activeValues}
                onToggle={toggleHandler}
              />
            )}
          </div>
        );
      })}
    </div>
  );
};