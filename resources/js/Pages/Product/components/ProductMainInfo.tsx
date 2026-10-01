import React from 'react';
import {H1, Text} from '@/shared/components/ui/Typography';
import Badge from "@shared/components/ui/Badge";
import {checkDevMode} from '@/shared/lib/dev';
import {Calculator, PhoneCall} from 'lucide-react';
import {siteConfig} from '@/shared/config/site';

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
    <div className="mb-8 border-b border-[#E2E6EA] pb-8">
      {isDev && (
        <span className="inline-block mb-4 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-mono font-bold uppercase">
          API Item
        </span>
      )}

      <H1 className="font-heading font-extrabold text-[#08274D] text-[30px] md:text-[38px] leading-tight mb-4">
        {name}
      </H1>

      <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-8 bg-slate-50/70 p-5 rounded-lg border border-slate-200/80">
        <div>
          <Text className="text-[11px] font-heading font-bold text-[#8B9198] uppercase tracking-wider mb-1">
            Стоимость материала за м²
          </Text>

          <div className="text-[32px] md:text-[36px] font-heading font-black text-[#08274D] leading-none flex items-baseline gap-1.5">
            {priceFrom > 0 ? (
              <>
                <span>{formattedNumber}</span>
                <span className="text-sm md:text-base font-normal text-[#696973] lowercase">
                  {currencySymbol} / м²
                </span>
              </>
            ) : (
              <Badge variant="gray"
                     className="!bg-white !border-slate-200 !text-slate-600 !shadow-none !px-3 !py-1 text-xs font-heading font-bold uppercase rounded-md">
                Цена по запросу
              </Badge>
            )}
          </div>
        </div>

        <div className="flex items-center gap-3">
          <a
            href={siteConfig.contacts.orderCalc?.href || "#"}
            target="_blank"
            rel="noreferrer"
            className="h-10 px-5 rounded-lg bg-[#08274D] hover:bg-[#EF5042] text-white font-heading font-semibold text-xs uppercase tracking-wider transition-all flex items-center gap-2 shadow-2xs cursor-pointer"
          >
            <Calculator className="w-4 h-4" /> Рассчитать изделие
          </a>
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
