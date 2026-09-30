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
    }, 5500);

    return () => clearInterval(interval);
  }, [isPaused, current]);

  const handlePrev = () => {
    setCurrent((prev) => (prev - 1 + SLIDES.length) % SLIDES.length);
  };

  const handleNext = () => {
    setCurrent((prev) => (prev + 1) % SLIDES.length);
  };

  const activeSlide = SLIDES[current];

  return (
    <section className="w-full relative z-0 bg-white border-b border-[#E2E6EA] overflow-hidden">
      <div
        className="relative w-full min-h-[440px] sm:min-h-[500px] lg:min-h-[560px] flex items-center overflow-hidden select-none"
        onMouseEnter={() => setIsPaused(true)}
        onMouseLeave={() => setIsPaused(false)}
      >
        {/* Анимированная полоса таймера как в MasterSlider */}
        <div className="absolute top-0 left-0 w-full h-[3px] bg-slate-200/40 z-30 overflow-hidden">
          <div
            key={current}
            className={cn(
              "h-full bg-[#EF5042] transition-all duration-[5500ms] ease-linear",
              isPaused ? "opacity-60" : "w-full"
            )}
            style={{ width: isPaused ? undefined : '100%' }}
          />
        </div>

        {/* Фоновые изображения слайдов с плавной кросс-анимацией */}
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
            {/* Адаптивный градиент подложки для читаемости текста */}
            <div
              className={cn(
                "absolute inset-0 transition-colors duration-500",
                slide.isDarkText
                  ? "bg-gradient-to-r from-white/95 via-white/85 to-white/20 md:to-transparent"
                  : "bg-gradient-to-r from-black/85 via-black/60 to-black/20 md:to-transparent"
              )}
            />
          </div>
        ))}

        {/* Содержимое текущего активного слайда, выровненное по контейнеру сайта */}
        <div className="relative z-10 max-w-[1440px] mx-auto w-full px-6 md:px-12 lg:px-16 py-12 md:py-16 flex flex-col items-start text-left">
          <div className="flex items-center gap-2 mb-3">
            <span className="w-1.5 h-5 bg-[#EF5042] rounded-full inline-block shrink-0" />
            <span
              className={cn(
                "text-[11px] md:text-[12px] font-heading font-extrabold uppercase tracking-widest",
                activeSlide.isDarkText ? "text-[#08274D]" : "text-white/90"
              )}
            >
              {activeSlide.tag}
            </span>
          </div>

          <h1
            className={cn(
              "font-heading font-bold text-[24px] sm:text-[32px] lg:text-[38px] leading-[1.2] mb-4 tracking-tight transition-colors duration-500",
              activeSlide.isDarkText ? "text-[#08274D]" : "text-white"
            )}
          >
            {activeSlide.title}
          </h1>

          <p
            className={cn(
              "font-sans text-[15px] sm:text-[17px] leading-relaxed mb-8 max-w-xl transition-colors duration-500",
              activeSlide.isDarkText ? "text-[#486581]" : "text-slate-200"
            )}
          >
            {activeSlide.subtitle}
          </p>

          <div className="flex flex-wrap items-center gap-3.5">
            <Link
              href={route('catalog')}
              className="inline-flex items-center gap-2 px-7 py-3.5 bg-[#08274D] hover:bg-[#EF5042] text-white font-heading font-bold text-[12px] md:text-[13px] uppercase tracking-wider rounded-xl transition-all shadow-sm active:scale-[0.98] cursor-pointer"
            >
              Каталог камня <ArrowRight className="w-4 h-4" />
            </Link>

            <Link
              href={route('calculator.show')}
              className={cn(
                "inline-flex items-center gap-2 px-6 py-3.5 font-heading font-bold text-[12px] md:text-[13px] uppercase tracking-wider rounded-xl transition-all shadow-xs active:scale-[0.98] border cursor-pointer",
                activeSlide.isDarkText
                  ? "bg-white/95 hover:bg-white text-[#08274D] border-[#E2E6EA] hover:border-[#08274D]"
                  : "bg-white/10 hover:bg-white/20 text-white border-white/20 hover:border-white"
              )}
            >
              <Calculator className="w-4 h-4" /> Калькулятор
            </Link>
          </div>
        </div>

        {/* Боковые кнопки переключения слайдов (Next / Prev) */}
        <div className="hidden md:flex flex-col gap-2 absolute right-6 top-1/2 -translate-y-1/2 z-20">
          <button
            onClick={handlePrev}
            aria-label="Предыдущий слайд"
            className="w-10 h-10 rounded-full bg-white/90 hover:bg-white text-[#08274D] hover:text-[#EF5042] border border-[#E2E6EA] flex items-center justify-center shadow-md transition-all cursor-pointer active:scale-95"
          >
            <ChevronLeft className="w-5 h-5" />
          </button>
          <button
            onClick={handleNext}
            aria-label="Следующий слайд"
            className="w-10 h-10 rounded-full bg-white/90 hover:bg-white text-[#08274D] hover:text-[#EF5042] border border-[#E2E6EA] flex items-center justify-center shadow-md transition-all cursor-pointer active:scale-95"
          >
            <ChevronRight className="w-5 h-5" />
          </button>
        </div>

        {/* Буллеты пагинации слайдов внизу */}
        <div className="absolute bottom-6 left-6 md:left-12 z-20 flex items-center gap-2">
          {SLIDES.map((slide, idx) => (
            <button
              key={slide.id}
              onClick={() => setCurrent(idx)}
              className={cn(
                "transition-all duration-300 rounded-full cursor-pointer h-2.5",
                current === idx
                  ? "w-8 bg-[#EF5042]"
                  : "w-2.5 bg-slate-400/60 hover:bg-slate-500"
              )}
              aria-label={`Перейти к слайду ${idx + 1}`}
            />
          ))}
        </div>
      </div>
    </section>
  );
}