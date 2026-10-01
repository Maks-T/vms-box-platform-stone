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

  return (
    <div className="w-full space-y-7">
      {displayableFilters.map((filter) => {
        
        const filterType = filter.settings?.filter_type || 'checkbox';
        const activeValues = activeFilters[filter.code] || [];
        const toggleHandler = (slug: string) => onToggle(filter.code, slug);

        return (
          <div key={filter.code} className="flex flex-col pb-6 border-b border-slate-200/60 last:border-b-0 last:pb-0">
            <h3 className="text-[12px] font-heading font-bold tracking-wider text-[#08274D] uppercase mb-3.5 select-none">
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
