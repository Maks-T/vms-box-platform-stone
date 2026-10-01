import React, { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { ArrowRight, Calculator, ChevronLeft, ChevronRight } from 'lucide-react';
import { cn } from '@/shared/lib/utils';
import { route } from 'ziggy-js';

interface Slide {
  id: number;
  image: string;
  image2x: string;
  tag: string;
  title: string;
  subtitle: string;
  isDarkText: boolean;
}

const SLIDES: Slide[] = [
  {
    id: 1,
    image: 'https://masterstone-spb.ru/image/cache/catalog/Banner/home-1-1280x600h.jpg',
    image2x: 'https://masterstone-spb.ru/image/cache/catalog/Banner/home-1-2560x1200h.jpg',
    tag: 'Собственное производство в СПб и Гатчине',
    title: 'МастерСтоун — производство изделий из натурального и искусственного камня в Санкт-Петербурге:',
    subtitle: 'столешницы, подоконники, ступени, камины и изделия по индивидуальным размерам.',
    isDarkText: true,
  },
  {
    id: 2,
    image: 'https://masterstone-spb.ru/image/cache/catalog/Banner/banstol22-1280x600w.jpg',
    image2x: 'https://masterstone-spb.ru/image/cache/catalog/Banner/banstol22-2560x1200w.jpg',
    tag: 'Каталог камня',
    title: 'Столешницы из натурального и искусственного камня',
    subtitle: 'Гранит, мрамор, кварцит и кварцевый агломерат для кухонь, ванных и барных зон.',
    isDarkText: true,
  },
  {
    id: 3,
    image: 'https://masterstone-spb.ru/image/cache/catalog/Banner/iskstol-1280x600w.jpg',
    image2x: 'https://masterstone-spb.ru/image/cache/catalog/Banner/iskstol-2560x1200w.jpg',
    tag: 'Специальное предложение',
    title: 'Хотите качественную столешницу по специальной цене?',
    subtitle: 'У нас для вас отличные новости! Распродажа остатков слэбов со склада в Санкт-Петербурге.',
    isDarkText: true,
  },
  {
    id: 4,
    image: 'https://masterstone-spb.ru/image/cache/catalog/Banner/rea_03_cover-1280x600h.jpg',
    image2x: 'https://masterstone-spb.ru/image/cache/catalog/Banner/rea_03_cover-2560x1200h.jpg',
    tag: 'Услуги под ключ',
    title: 'Облицовка натуральным камнем',
    subtitle: 'Облицовка полов, стен, каминных порталов, фасадов и входных групп любой сложности.',
    isDarkText: false,
  },
];

export function CatalogHeroBlock() {
  const [current, setCurrent] = useState(0);
  const [isPaused, setIsPaused] = useState(false);

  useEffect(() => {
    if (isPaused) return;

    const interval = setInterval(() => {
      setCurrent((prev) => (prev + 1) % SLIDES.length);
    }, 6000);

    return () => clearInterval(interval);
  }, [isPaused]);

  const handlePrev = () => {
    setCurrent((prev) => (prev - 1 + SLIDES.length) % SLIDES.length);
  };

  const handleNext = () => {
    setCurrent((prev) => (prev + 1) % SLIDES.length);
  };

  const activeSlide = SLIDES[current];

  return (
    <div className="w-full max-w-[1280px] mx-auto px-4 md:px-8 lg:px-10 pt-4 md:pt-6 mb-4">
      <div
        className="relative w-full rounded-xl border border-slate-200/90 overflow-hidden bg-[#08274D] shadow-xs min-h-[380px] sm:min-h-[420px] lg:min-h-[460px] flex items-center select-none"
        onMouseEnter={() => setIsPaused(true)}
        onMouseLeave={() => setIsPaused(false)}
      >
        {/* Фоновые широкоформатные фотографии фактуры камня */}
        {SLIDES.map((slide, idx) => (
          <div
            key={slide.id}
            className={cn(
              "absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out pointer-events-none",
              current === idx ? "opacity-100 z-0" : "opacity-0 -z-10"
            )}
          >
            <img
              src={slide.image}
              srcSet={`${slide.image} 1x, ${slide.image2x} 2x`}
              alt={slide.title}
              className="w-full h-full object-cover object-right md:object-center"
              draggable="false"
            />
            {/* Тонкий благородный градиент для контраста */}
            <div
              className={cn(
                "absolute inset-0 transition-colors duration-500 bg-gradient-to-r from-slate-950/70 via-slate-900/40 to-transparent md:from-slate-950/50"
              )}
            />
          </div>
        ))}

        {/* Парящая архитектурная карточка коллекции в стиле Vicostone / Lookbook */}
        <div className="relative z-10 p-6 sm:p-8 lg:p-10 w-full max-w-xl">
          <div className="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-xl border border-white/40 shadow-xl flex flex-col items-start text-left">
            <div className="flex items-center gap-2 mb-2.5">
              <span className="text-[10px] sm:text-[11px] font-heading font-bold uppercase tracking-[0.18em] text-[#9B6A38]">
                {activeSlide.tag}
              </span>
            </div>

            <h1 className="font-heading font-bold text-[20px] sm:text-[24px] lg:text-[27px] leading-[1.25] mb-3 text-[#08274D] tracking-tight">
              {activeSlide.title}
            </h1>

            <p className="font-sans text-[13px] sm:text-[14px] leading-relaxed mb-6 text-slate-600 line-clamp-3">
              {activeSlide.subtitle}
            </p>

            <div className="flex flex-wrap items-center gap-3">
              <Link
                href={route('catalog')}
                className="inline-flex items-center gap-2 px-5 py-2.5 bg-[#08274D] hover:bg-[#EF5042] text-white font-heading font-semibold text-[11px] uppercase tracking-wider rounded-lg transition-all shadow-2xs active:scale-[0.98] cursor-pointer"
              >
                Смотреть коллекцию <ArrowRight className="w-3.5 h-3.5" />
              </Link>

              <Link
                href={route('calculator.show')}
                className="inline-flex items-center gap-1.5 px-4 py-2.5 font-heading font-semibold text-[11px] uppercase tracking-wider rounded-lg transition-all text-[#08274D] hover:text-[#EF5042] border border-slate-200 hover:border-slate-300 cursor-pointer bg-white"
              >
                <Calculator className="w-3.5 h-3.5 text-[#9B6A38]" /> Расчет
              </Link>
            </div>
          </div>
        </div>

        {/* Боковые кнопки переключения слайдов */}
        <div className="hidden md:flex flex-col gap-2 absolute right-5 top-1/2 -translate-y-1/2 z-20">
          <button
            onClick={handlePrev}
            aria-label="Предыдущий слайд"
            className="w-8 h-8 rounded-lg bg-white/95 hover:bg-white text-[#08274D] hover:text-[#EF5042] border border-slate-200 flex items-center justify-center shadow-2xs transition-all cursor-pointer active:scale-95"
          >
            <ChevronLeft className="w-4 h-4" />
          </button>
          <button
            onClick={handleNext}
            aria-label="Следующий слайд"
            className="w-8 h-8 rounded-lg bg-white/95 hover:bg-white text-[#08274D] hover:text-[#EF5042] border border-slate-200 flex items-center justify-center shadow-2xs transition-all cursor-pointer active:scale-95"
          >
            <ChevronRight className="w-4 h-4" />
          </button>
        </div>

        {/* Тонкие индикаторы слайдов в стиле лукбука */}
        <div className="absolute bottom-4 right-6 md:right-8 z-20 flex items-center gap-1.5 bg-black/30 backdrop-blur-xs px-3 py-1.5 rounded-full">
          {SLIDES.map((slide, idx) => (
            <button
              key={slide.id}
              onClick={() => setCurrent(idx)}
              className={cn(
                "transition-all duration-300 rounded-full cursor-pointer h-1.5",
                current === idx
                  ? "w-6 bg-white"
                  : "w-1.5 bg-white/40 hover:bg-white/70"
              )}
              aria-label={`Перейти к слайду ${idx + 1}`}
            />
          ))}
        </div>
      </div>
    </div>
  );
}

export default CatalogHeroBlock;