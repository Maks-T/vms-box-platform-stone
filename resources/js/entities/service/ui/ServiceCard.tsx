import React from 'react';
import { Image as ImageIcon, Copy } from 'lucide-react';
import { toast } from 'sonner';
import { IconBox } from '@/shared/components/ui/IconBox';
import StatusBadge from '@/shared/components/ui/StatusBadge';
import GlassPanel from '@/shared/components/ui/GlassPanel';
import { ServiceMatrixItem, EavValueOption } from '@/types/catalog';
import { cn } from '@/shared/lib/utils';

interface ServiceCardProps {
  service: ServiceMatrixItem;
}

const MATERIAL_NAMES: Record<string, string> = {
  acrylic_stone: 'Акриловый камень',
  quartz_stone: 'Кварцевый агломерат',
};

export function ServiceCard({ service }: ServiceCardProps) {
  const handleCopy = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    navigator.clipboard.writeText(service.slug);
    toast.success(`Код ${service.slug} скопирован`);
  };

  const unitName = service.unit?.symbol || service.unit?.name || 'шт.';

  const tagsAttr = service.attributes?.service_tags?.value;
  const tags: EavValueOption[] = Array.isArray(tagsAttr) ? tagsAttr : [];

  return (
    <GlassPanel
      variant="default"
      padding="none"
      interactive={true}
      className="group flex flex-col h-full overflow-hidden bg-gradient-to-br from-[#111827] to-[#0B0F19] border-white/10 shadow-xl"
    >
      <div className="p-6 md:p-8 border-b border-white/5 flex gap-5 items-start">
        <IconBox variant="glass" size="lg" className="shrink-0 bg-white/5 border-white/10">
          {service.preview_picture ? (
            <img src={service.preview_picture} alt={service.name} className="w-full h-full object-cover rounded-xl" />
          ) : (
            <ImageIcon className="w-6 h-6 text-white/30" />
          )}
        </IconBox>

        <div className="flex flex-col gap-3 items-start">
          <button
            onClick={handleCopy}
            className="group/copy flex items-center gap-2 px-2.5 py-1 bg-white/5 hover:bg-white/10 rounded-md border border-white/10 transition-colors cursor-pointer"
            title="Копировать код"
          >
            <span className="text-white/50 group-hover/copy:text-white/90 text-[11px] font-mono lowercase tracking-wider transition-colors">
              {service.slug}
            </span>
            <Copy className="w-3 h-3 text-white/30 group-hover/copy:text-white/90 transition-colors" />
          </button>

          <h3 className="text-[16px] font-bold text-white leading-snug group-hover:text-primary transition-colors">
            {service.name}
          </h3>
        </div>
      </div>

      <div className="p-6 md:p-8 flex-1 bg-white/[0.01]">
        <div className="text-[11px] font-bold uppercase tracking-widest text-white/30 mb-5">
          Базовая стоимость ({unitName})
        </div>

        <div className="flex flex-col gap-3">
          {Object.keys(service.prices || {}).length > 0 ? (
            Object.entries(service.prices).map(([materialSlug, price]) => (
              <div key={materialSlug} className="flex items-center justify-between border-b border-white/5 pb-3 last:border-0 last:pb-0">
                <span className="text-[14px] font-medium text-white/60">
                  {MATERIAL_NAMES[materialSlug] || materialSlug}
                </span>
                <span className="text-[15px] font-black text-[#3D98FF]">
                  {price > 0
                    ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 }).format(price)
                    : 'Бесплатно'}
                </span>
              </div>
            ))
          ) : (
            <div className="text-sm text-white/30 italic">Цены не заданы</div>
          )}
        </div>
      </div>

      {tags.length > 0 && (
        <div className="px-6 py-5 border-t border-white/5 flex flex-wrap gap-2 bg-white/[0.02]">
          {tags.map(tag => {
            const isAddon = tag.slug === 'addon';

            return (
              <StatusBadge key={tag.slug} variant={isAddon ? "warning" : "success"} className="px-2.5 py-1.5">
                <span className={cn(
                  "uppercase tracking-widest text-[9px] font-bold",
                  isAddon ? "text-amber-400" : "text-emerald-400"
                )}>
                  {tag.name}
                </span>
              </StatusBadge>
            );
          })}
        </div>
      )}
    </GlassPanel>
  );
}
