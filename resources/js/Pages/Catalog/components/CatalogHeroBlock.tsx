import React, { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { cn } from '@/shared/lib/utils';

interface Slide {
  id: number;
  image: string;
  badge: string;
  title: string;
  description: string;
  detailLink: string;
}

const SLIDES: Slide[] = [
  {
    id: 1,
    image: 'https://quartz-master.com/upload/iblock/db5/q1jewr8y5fymjxe319c5stx51it4md39.jpg',
    badge: 'Премиум проект',
    title: 'Кухонная столешница и остров с каменной мойкой из кварца AvantQuartz Корсика',
    description: 'Бесшовное соединение элементов, интегрированная мойка из цельного камня и идеальная обработка фаски на немецком ЧПУ-оборудовании.',
    detailLink: route('catalog'),
  },
  {
    id: 2,
    image: 'https://masterstone-spb.ru/image/cache/catalog/Banner/banstol22-1280x600w.jpg',
    badge: 'Кварцевый агломерат',
    title: 'Столешницы и стеновые панели из кварца и широкоформатной керамики',
    description: 'Стойкость к царапинам, нулевое водопоглощение и естественный природный рисунок мрамора и гранита.',
    detailLink: route('catalog'),
  },
  {
    id: 3,
    image: 'https://quartz-master.com/upload/iblock/55f/nr48c75o2a8xqgurhg0pgbth27lrsa9w.jpg',
    badge: 'Склад в Москве',
    title: 'Коллекция слэбов АВАРУС R677 Московская Ночь в наличии',
    description: 'Прямые поставки с завода, изготовление изделий любой геометрической сложности по индивидуальным чертежам за 7 дней.',
    detailLink: route('catalog'),
  },
];

export function CatalogHeroBlock() {
  const [current, setCurrent] = useState(0);
  const [isPaused, setIsPaused] = useState(false);

  useEffect(() => {
    if (isPaused) return;
    const interval = setInterval(() => {
      setCurrent((prev) => (prev + 1) % SLIDES.length);
    }, 6500);
    return () => clearInterval(interval);
  }, [isPaused]);

  const handlePrev = () => {
    setCurrent((prev) => (prev - 1 + SLIDES.length) % SLIDES.length);
  };

  const handleNext = () => {
    setCurrent((prev) => (prev + 1) % SLIDES.length);
  };

  const active = SLIDES[current];

  return (
    <section
      className="relative bg-[#212B36] text-white min-h-[520px] lg:min-h-[580px] flex items-center overflow-hidden mb-8 lg:mb-10 w-full"
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
    >
      {/* Фоновые широкоформатные фото слэбов и проектов */}
      {SLIDES.map((slide, idx) => (
        <div
          key={slide.id}
          className={cn(
            "absolute inset-0 z-0 transition-opacity duration-700 ease-in-out pointer-events-none",
            current === idx ? "opacity-100" : "opacity-0"
          )}
        >
          <img
            src={slide.image}
            alt={slide.title}
            className="w-full h-full object-cover object-center brightness-75"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-[#212B36]/90 via-[#212B36]/65 to-transparent" />
        </div>
      ))}

      <div className="max-w-[1412px] mx-auto px-4 md:px-8 w-full relative z-10 py-14 lg:py-20">
        <div className="max-w-[760px] text-left">
          {/* Бейдж статуса */}
          <div className="inline-flex items-center gap-2 px-3 py-1 bg-[#25CED1]/20 border border-[#25CED1]/40 rounded-md text-[#25CED1] text-xs font-semibold tracking-wider uppercase mb-5">
            <span className="w-2 h-2 rounded-full bg-[#25CED1] animate-pulse" />
            {active.badge}
          </div>

          {/* Заголовок проекта */}
          <h1 className="font-heading font-extrabold text-2xl sm:text-3xl lg:text-5xl leading-[1.15] tracking-tight text-white mb-5">
            {active.title}
          </h1>

          {/* Описание */}
          <p className="text-gray-300 text-sm sm:text-base lg:text-lg leading-relaxed max-w-[620px] mb-8 font-normal">
            {active.description}
          </p>

          {/* Кнопки действий: Красный CTA + Дизайнер */}
          <div className="flex flex-wrap items-center gap-4">
            <Link
              href={active.detailLink}
              className="inline-flex items-center justify-center px-7 py-3.5 bg-[#ED1C24] hover:bg-white hover:text-[#212B36] text-white font-heading font-bold text-xs uppercase tracking-wider rounded-md transition-all duration-200 shadow-md cursor-pointer"
            >
              Подробнее о проекте
            </Link>
            <Link
              href={route('calculator.show')}
              className="inline-flex items-center justify-center px-7 py-3.5 bg-transparent border border-white/50 hover:border-[#25CED1] hover:text-[#25CED1] text-white font-heading font-bold text-xs uppercase tracking-wider rounded-md transition-all duration-200 backdrop-blur-sm cursor-pointer"
            >
              Рассчитать онлайн
            </Link>
          </div>
        </div>
      </div>

      {/* Пагинация и стрелки слайдера */}
      <div className="max-w-[1412px] mx-auto px-4 md:px-8 absolute bottom-6 inset-x-0 z-10 flex items-center justify-between">
        {/* Буллеты */}
        <div className="flex items-center gap-2">
          {SLIDES.map((_, idx) => (
            <button
              key={idx}
              onClick={() => setCurrent(idx)}
              className={cn(
                "h-2 rounded-full transition-all duration-300 cursor-pointer",
                current === idx ? "w-8 bg-[#25CED1]" : "w-2 bg-white/40 hover:bg-white"
              )}
              aria-label={`Слайд ${idx + 1}`}
            />
          ))}
        </div>

        {/* Стрелки Prev / Next */}
        <div className="hidden sm:flex items-center gap-2">
          <button
            type="button"
            onClick={handlePrev}
            className="w-9 h-9 rounded-md bg-[#212B36]/80 hover:bg-[#25CED1] hover:text-[#212B36] text-white border border-white/20 flex items-center justify-center transition-colors cursor-pointer"
            aria-label="Назад"
          >
            <ChevronLeft className="w-4 h-4" />
          </button>
          <button
            type="button"
            onClick={handleNext}
            className="w-9 h-9 rounded-md bg-[#212B36]/80 hover:bg-[#25CED1] hover:text-[#212B36] text-white border border-white/20 flex items-center justify-center transition-colors cursor-pointer"
            aria-label="Вперед"
          >
            <ChevronRight className="w-4 h-4" />
          </button>
        </div>
      </div>
    </section>
  );
}

export default CatalogHeroBlock;