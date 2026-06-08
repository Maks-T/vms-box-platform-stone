import React from 'react';
import {CheckCircle2} from 'lucide-react';
import {H1, Text} from '@/shared/components/ui/Typography';
import StatusBadge from '@/shared/components/ui/StatusBadge';
import Badge from "@shared/components/ui/Badge";

interface Props {
  name: string;
  priceFrom: number;
}

export function ProductMainInfo({name, priceFrom}: Props) {
  const formattedPrice = new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 0
  }).format(priceFrom);

  return (
    <div className="mb-8 border-b border-border pb-8">
      <StatusBadge variant="success" className="mb-6 w-max">
        <div className="flex items-center gap-1.5 whitespace-nowrap">
          <span>API Data Object</span>
        </div>
      </StatusBadge>

      <H1 className="!text-foreground !text-[32px] md:!text-[44px] mb-6">
        {name}
      </H1>

      <div className="flex items-end gap-6">
        <div>
          <Text className="text-[11px] !text-muted-foreground font-bold uppercase tracking-widest mb-2">
            Базовая цена от
          </Text>
          <div className="text-[32px] font-black text-primary leading-none">
            {priceFrom > 0
              ? formattedPrice
              : <Badge variant="gray"
                       className="!bg-muted !border-border !text-muted-foreground !shadow-none !px-3 !py-1 text-xs">
                Нет в наличии
              </Badge>}
          </div>
        </div>
      </div>
    </div>
  );
}