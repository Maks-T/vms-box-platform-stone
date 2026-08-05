import React, {ReactNode} from 'react';
import SectionLayout from '@/shared/components/layouts/SectionLayout';

interface PageHeroProps {
  badge: string;
  title: ReactNode;
  description: string;
  imageSrc?: string;
  bgImageSrc?: string;
  className?: string;
  showCta?: boolean;
  ctaText?: string;
  ctaHref?: string;
}

export function PageHero({
                           badge,
                           title,
                           description,
                           imageSrc = '/images/acrylic-wave-stone.png',
                           bgImageSrc = '/images/bg-stone.png',
                           className,
                           showCta = true,
                           ctaText = 'Рассчитать проект',
                           ctaHref = '/calculator'
                         }: PageHeroProps) {
  return (
    <SectionLayout
      containerVariant="content"
      noPadding={true}
      className={className ?? "py-3 md:py-5"}
    >
      <div
        className="bg-white rounded-lg border border-border p-6 sm:p-10 lg:p-12 flex flex-col md:flex-row items-center justify-between gap-8 w-full shadow-sm relative overflow-hidden">
        <div
          className="absolute inset-0 z-0 bg-cover bg-right opacity-80 pointer-events-none"
          style={{backgroundImage: `url('${bgImageSrc}')`}}
        />

        <div
          className="absolute inset-0 z-0 bg-gradient-to-r from-white via-white/90 to-transparent pointer-events-none"/>

        <div className="space-y-4 max-w-xl relative z-10">
          <div
            className="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-zinc-100 border border-zinc-200/80 text-zinc-700 text-[10px] font-bold uppercase tracking-widest self-start">
            <span className="w-1.5 h-1.5 rounded-full bg-red-600"/>
            {badge}
          </div>

          <h1 className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-zinc-900 tracking-tight leading-tight">
            {title}
          </h1>

          <p className="text-xs sm:text-sm text-zinc-600 font-medium leading-relaxed">
            {description}
          </p>

          {showCta && (
            <div className="pt-2">
              <a
                href={ctaHref}
                className="inline-flex items-center justify-center px-5 py-2.5 rounded bg-[#B92B3A] hover:bg-[#9E2230] text-white text-xs font-bold tracking-wider uppercase transition-all shadow-sm active:scale-95"
              >
                {ctaText}
              </a>
            </div>
          )}
        </div>

        <div
          className="w-full sm:w-80 md:w-96 lg:w-[400px] h-44 sm:h-52 md:h-56 shrink-0 flex items-center justify-center relative z-10">
          <img
            src={imageSrc}
            alt=""
            className="max-w-full max-h-full object-contain drop-shadow-[0_20px_30px_rgba(0,0,0,0.15)] hover:scale-105 transition-transform duration-500 cursor-pointer"
          />
        </div>
      </div>
    </SectionLayout>
  );
}