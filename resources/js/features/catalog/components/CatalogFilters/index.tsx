import React, { useState } from 'react';
import { CheckboxFilter } from './CheckboxFilter';
import { ColorFilter } from './ColorFilter';
import { Filter } from '@/types/catalog';
import { ChevronDown, Filter as FilterIcon } from 'lucide-react';
import { cn } from '@/shared/lib/utils';

interface Props {
  filters: Filter[];
  activeFilters: Record<string, string[]>;
  onToggle: (code: string, slug: string) => void;
}

export const CatalogFilters = ({ filters, activeFilters, onToggle }: Props) => {
  const displayableFilters = filters.filter((f) => f.options && f.options.length > 0);
  const [collapsed, setCollapsed] = useState<Record<string, boolean>>({});

  const toggleSection = (code: string) => {
    setCollapsed((prev) => ({ ...prev, [code]: !prev[code] }));
  };

  const totalActive = Object.values(activeFilters).reduce((sum, list) => sum + (list?.length || 0), 0);

  return (
    <div className="space-y-6 text-left">
      {/* Главный блок фильтрации Aspro */}
      <div className="border border-[#E5E5E5] rounded-md bg-white overflow-hidden shadow-2xs">
        {/* Заголовок фильтра */}
        <div className="px-5 py-3.5 border-b border-[#E5E5E5] flex items-center justify-between text-xs font-bold text-[#212B36] uppercase tracking-wider bg-[#F8F8F8]">
          <div className="flex items-center gap-2">
            <FilterIcon className="w-3.5 h-3.5 text-gray-700" />
            <span>Фильтр</span>
          </div>
          {totalActive > 0 && (
            <span className="px-2 py-0.5 rounded-full bg-[#25CED1]/20 text-[#148587] text-[10px] font-bold">
              {totalActive}
            </span>
          )}
        </div>

        {/* Секции фильтров */}
        <div className="divide-y divide-[#E5E5E5]">
          {displayableFilters.map((filter) => {
            const filterType = filter.settings?.filter_type || (filter.code.includes('color') ? 'color' : 'checkbox');
            const activeValues = activeFilters[filter.code] || [];
            const isCollapsed = collapsed[filter.code];
            const toggleHandler = (slug: string) => onToggle(filter.code, slug);

            return (
              <div key={filter.code} className="relative">
                <div
                  onClick={() => toggleSection(filter.code)}
                  className="px-5 py-3.5 flex items-center justify-between font-semibold text-sm text-[#212B36] bg-[#F8F8F8]/50 hover:bg-[#F8F8F8] transition-colors cursor-pointer select-none"
                >
                  <span>{filter.name}</span>
                  <ChevronDown
                    className={cn(
                      "w-3.5 h-3.5 text-gray-400 transition-transform duration-200",
                      isCollapsed && "-rotate-90"
                    )}
                  />
                </div>

                {!isCollapsed && (
                  <div className="px-5 py-3 max-h-[220px] overflow-y-auto custom-scrollbar">
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
                )}
              </div>
            );
          })}
        </div>

        {/* Кнопка "Показать" */}
        <div className="p-4 border-t border-[#E5E5E5] bg-[#F8F8F8]/60">
          <button
            type="button"
            className="w-full py-2.5 bg-[#ED1C24] hover:bg-[#212B36] text-white rounded-md font-heading font-bold text-xs uppercase tracking-wider transition-colors shadow-sm cursor-pointer"
          >
            Показать {totalActive > 0 ? `(${totalActive})` : ''}
          </button>
        </div>
      </div>

      {/* Боковой баннер: Мойки и аксессуары */}
      <div className="border border-[#E5E5E5] rounded-md bg-white p-4 shadow-2xs space-y-3 hidden lg:block">
        <div className="overflow-hidden rounded-md bg-[#F8F8F8] aspect-4/3">
          <img
            src="https://quartz-master.com/upload/iblock/bca/sdsni1069c3xq8jpd9d1leu100sont1i.jpg"
            alt="Мойки и смесители"
            className="w-full h-full object-cover"
          />
        </div>
        <p className="text-xs text-gray-500 leading-relaxed">
          Также у нас Вы можете приобрести кухонные мойки, смесители и аксессуары Omoikiri, Blanco, Alveus, SCHOCK по специальным ценам.
        </p>
      </div>
    </div>
  );
};

export default CatalogFilters;