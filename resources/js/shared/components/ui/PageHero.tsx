import React, {ReactNode} from 'react';
import SectionLayout from '@/shared/components/layouts/SectionLayout';
import WaveBackground from '@/shared/components/ui/WaveBackground';
import StatusBadge from '@/shared/components/ui/StatusBadge';

interface PageHeroProps {
  badge: string;
  title: ReactNode;
  description: string;
  badgeVariant?: 'blue' | 'success' | 'warning';
  className?: string;
}

export function PageHero({
                           badge,
                           title,
                           description,
                           badgeVariant = 'blue',
                           className
                         }: PageHeroProps) {
  return (
    <SectionLayout
      bg="bg-[#0B0F19]"
      bgElement={<WaveBackground/>}
      containerVariant="content"
      noPadding={true}
      className={className ?? "py-1 md:py-2"}
    >
      <div
        className="flex flex-col md:flex-row md:items-center justify-between gap-6 md:gap-12 py-8 md:py-12 px-6 md:px-12 w-full">

        <div className="flex flex-col gap-3">
          <StatusBadge variant={badgeVariant} className="self-start !py-1 !px-3.5 !text-[11px]">
            {badge}
          </StatusBadge>

          <h1 className="text-2xl md:text-3xl font-bold text-white tracking-tight">
            {title}
          </h1>
        </div>

        <p className="text-sm md:text-base text-slate-400 max-w-xl leading-relaxed">
          {description}
        </p>

      </div>
    </SectionLayout>
  );
}