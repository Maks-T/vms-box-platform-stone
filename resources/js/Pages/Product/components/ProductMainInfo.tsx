import React from 'react';
import {H1, Text} from '@/shared/components/ui/Typography';
import StatusBadge from '@/shared/components/ui/StatusBadge';
import Badge from "@shared/components/ui/Badge";
import {checkDevMode} from '@/shared/lib/dev';

interface Props {
  name: string;
  priceFrom: number;
  bootstrapConfig?: any;
  shortDescription?: string | null; // Добавили краткое описание
  description?: string | null;      // Добавили полное описание
}

export function ProductMainInfo({name, priceFrom, bootstrapConfig, shortDescription, description}: Props) {
  const isDev = checkDevMode();
  const currencySymbol = bootstrapConfig?.base_currency?.symbol_native || bootstrapConfig?.base_currency?.symbol || 'Br';

  const formattedNumber = priceFrom > 0
    ? new Intl.NumberFormat('ru-RU', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }).format(priceFrom)
    : '';

  return (
    <div className="mb-8 border-b border-border pb-8">
      {isDev && (
        <StatusBadge variant="success" className="mb-6 w-max">
          <div className="flex items-center gap-1.5 whitespace-nowrap">
            <span>API Data Object</span>
          </div>
        </StatusBadge>
      )}

      <H1 className="!text-foreground !text-[32px] md:!text-[44px] mb-6">
        {name}
      </H1>

      <div className="flex items-end gap-6 mb-8">
        <div>
          <Text className="text-[11px] !text-muted-foreground font-bold uppercase tracking-widest mb-2">
            Базовая цена от
          </Text>

          <div className="text-[32px] font-black text-primary leading-none flex items-baseline gap-1.5">
            {priceFrom > 0 ? (
              <>
                <span>{formattedNumber}</span>
                <span className="text-sm md:text-base font-normal text-muted-foreground lowercase">
                  {currencySymbol}
                </span>
              </>
            ) : (
              <Badge variant="gray"
                     className="!bg-muted !border-border !text-muted-foreground !shadow-none !px-3 !py-1 text-xs">
                Нет в наличии
              </Badge>
            )}
          </div>
        </div>
      </div>

      {/* Рендеринг краткого описания товара (анонса) */}
      {shortDescription && (
        <div className="text-sm text-slate-500 leading-relaxed max-w-2xl mb-6 italic">
          {shortDescription}
        </div>
      )}

      {/* Рендеринг полного описания товара с поддержкой HTML */}
      {description && (
        <div
          className="text-sm text-slate-600 leading-relaxed max-w-2xl border-t border-border/50 pt-6 prose prose-slate"
          dangerouslySetInnerHTML={{__html: description}}
        />
      )}
    </div>
  );
}
