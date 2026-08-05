import React from 'react';
import {Head} from '@inertiajs/react';
import MainLayout from '@/layouts/MainLayout';
import SectionLayout from '@/shared/components/layouts/SectionLayout';
import {ShieldCheck, Truck, Clock, Award, Wrench, Package, CheckCircle2} from 'lucide-react';
import {siteConfig} from '@/shared/config/site';

export default function AboutIndex() {
  const {company, contacts} = siteConfig;

  const stats = [
    {value: '16+ лет', label: 'Опыта работы с камнем', desc: 'На рынке с 2008 года'},
    {value: 'от 3 дней', label: 'Сроки изготовления', desc: 'Собственный цех в Москве'},
    {value: '2 года', label: 'Гарантия на изделия', desc: 'И материалы от фабрик'},
    {value: '1 день', label: 'Доставка и монтаж', desc: 'Установка за один выезд'},
  ];

  const advantages = [
    {
      icon: Wrench,
      title: 'Собственное производство в Москве',
      desc: 'Современный станочный парк и раскроечные цеха позволяющие изготавливать изделия любой сложности.'
    },
    {
      icon: ShieldCheck,
      title: 'Закрепленная бригада на заказ',
      desc: 'Один ответственный мастер ведет весь цикл — замер, изготовление, доставку и монтаж без посредников.'
    },
    {
      icon: Package,
      title: 'Прямые поставки камня',
      desc: 'Партнерские соглашения с официальными дистрибьюторами Staron, Corian, Grandex, Avant Quartz, Caesarstone.'
    },
    {
      icon: Truck,
      title: 'Монтаж в 1 день',
      desc: 'Доставляем готовое изделие и проводим чистый монтаж со шлифовкой стыков в день доставки.'
    },
    {
      icon: Clock,
      title: 'Оперативный выезд на замер',
      desc: 'Замерщик приезжает с реальными образцами камня, снимает точные размеры и консультирует по фактурам.'
    },
    {
      icon: Award,
      title: 'Распродажа остатков слэбов',
      desc: 'Постоянное наличие мерных остатков камня на складе со скидками до 50% для небольших столешниц и подоконников.'
    }
  ];

  const brands = [
    'Grandex', 'Staron', 'LG Hi-Macs', 'Corian', 'Hanex', 'Kerrock',
    'Avant Quartz', 'Caesarstone', 'Belenco', 'Vicostone', 'Radianz', 'Technistone'
  ];

  return (
    <MainLayout headerOverlaps={false}>
      <Head title="О компании и производстве — Столешка.Ру"/>

      <div className="pt-4 md:pt-6">
        <SectionLayout containerVariant="content" noPadding={true}>
          <div
            className="bg-white rounded-lg border border-border p-6 sm:p-10 lg:p-12 shadow-sm relative overflow-hidden">
            <div
              className="absolute inset-0 z-0 bg-cover bg-right opacity-70 pointer-events-none"
              style={{backgroundImage: "url('/images/bg-stone.png')"}}
            />
            <div
              className="absolute inset-0 z-0 bg-gradient-to-r from-white via-white/90 to-transparent pointer-events-none"/>

            <div className="space-y-4 max-w-2xl relative z-10">
              <div
                className="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-zinc-100 border border-zinc-200/80 text-zinc-700 text-[10px] font-bold uppercase tracking-widest self-start">
                <span className="w-1.5 h-1.5 rounded-full bg-red-600"/>
                Собственное производство с 2008 года
              </div>

              <h1
                className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-zinc-900 tracking-tight leading-tight">
                Производственная мануфактура <span className="text-[#B92B3A]">Столешка Ру</span>
              </h1>

              <p className="text-xs sm:text-sm text-zinc-600 font-medium leading-relaxed">
                Мы специализируемся на изготовлении кухонных столешниц, раковин, подоконников и стеновых панелей из
                акрилового и кварцевого камня. Собственное производство в Москве позволяет гарантировать низкие цены без
                торговых наценок.
              </p>

              <div className="pt-2 flex flex-wrap gap-3">
                <a
                  href="/calculator"
                  className="inline-flex items-center justify-center px-5 py-2.5 rounded bg-[#B92B3A] hover:bg-[#9E2230] text-white text-xs font-bold tracking-wider uppercase transition-all shadow-sm active:scale-95"
                >
                  Рассчитать проект
                </a>
                <a
                  href="/?family=stone"
                  className="inline-flex items-center justify-center px-5 py-2.5 rounded bg-white border border-zinc-300 hover:border-zinc-900 text-zinc-900 text-xs font-bold tracking-wider uppercase transition-all"
                >
                  Перейти в каталог
                </a>
              </div>
            </div>
          </div>
        </SectionLayout>
      </div>

      <SectionLayout containerVariant="content" noPadding={true} className="py-6">
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          {stats.map((st, idx) => (
            <div key={idx}
                 className="bg-white p-5 rounded border border-border shadow-xs flex flex-col justify-between">
              <div className="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight mb-1">
                {st.value}
              </div>
              <div>
                <div className="text-xs font-bold text-zinc-800">{st.label}</div>
                <div className="text-[11px] text-zinc-400 font-medium mt-0.5">{st.desc}</div>
              </div>
            </div>
          ))}
        </div>
      </SectionLayout>

      <SectionLayout containerVariant="content" noPadding={true} className="py-4">
        <div className="bg-white p-6 sm:p-8 rounded border border-border space-y-6">
          <div className="max-w-2xl space-y-2">
            <span className="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block">
              Принцип ответственности
            </span>
            <h2 className="text-xl sm:text-2xl font-bold text-zinc-900 tracking-tight">
              Единая бригада на весь цикл заказа
            </h2>
            <p className="text-xs sm:text-sm text-zinc-600 font-medium leading-relaxed">
              На каждый индивидуальный заказ выделяется одна специальная бригада мастеров. Они ведут полностью весь
              цикл: проводит замер, изготавливает изделия в цеху, осуществляет доставку и проводит финальный монтаж.
              Такой подход исключает риск недопонимания деталей и появление рекламаций.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-4 border-t border-zinc-100">
            {advantages.map((adv, idx) => {
              const Icon = adv.icon;
              return (
                <div key={idx} className="p-4 rounded border border-zinc-100 bg-zinc-50/50 flex flex-col gap-2">
                  <div
                    className="w-8 h-8 rounded bg-white border border-zinc-200 flex items-center justify-center text-zinc-900 shrink-0">
                    <Icon className="w-4 h-4"/>
                  </div>
                  <h3 className="text-xs font-bold text-zinc-900">{adv.title}</h3>
                  <p className="text-[11px] text-zinc-500 font-medium leading-relaxed">{adv.desc}</p>
                </div>
              );
            })}
          </div>
        </div>
      </SectionLayout>

      <SectionLayout containerVariant="content" noPadding={true} className="py-4">
        <div className="bg-white p-6 sm:p-8 rounded border border-border space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="text-xs font-bold text-zinc-400 uppercase tracking-widest">
              Официальные бренды камня
            </h3>
            <span className="text-[11px] text-zinc-400 font-medium">100% сертифицированное сырье</span>
          </div>
          <div className="flex flex-wrap items-center gap-2 pt-2">
            {brands.map((b, idx) => (
              <span key={idx}
                    className="px-3.5 py-1.5 rounded bg-zinc-50 border border-zinc-200/80 text-xs font-bold text-zinc-800">
                {b}
              </span>
            ))}
          </div>
        </div>
      </SectionLayout>

      <SectionLayout containerVariant="content" noPadding={true} className="py-4 mb-8">
        <div
          className="bg-white p-6 sm:p-8 rounded border border-border grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
          <div className="space-y-3">
            <span className="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block">
              Реквизиты и контакты
            </span>
            <h3 className="text-xl font-bold text-zinc-900">Официальная информация</h3>
            <div className="space-y-1.5 text-xs text-zinc-600 font-medium leading-relaxed">
              <div><strong className="text-zinc-900">Организация:</strong> ООО «СТОЛЕШКА»</div>
              <div><strong className="text-zinc-900">ИНН:</strong> 7728345210 / <strong
                className="text-zinc-900">ОГРН:</strong> 1167746720192
              </div>
              <div><strong className="text-zinc-900">Адрес офиса и склада:</strong> г. Москва, ул. Намёткина, д. 10Б
              </div>
              <div><strong
                className="text-zinc-900">Телефон:</strong> {contacts.phone.label} / {contacts.phoneAdd?.label}</div>
              <div><strong className="text-zinc-900">Email:</strong> {contacts.email.label}</div>
              <div><strong className="text-zinc-900">График работы:</strong> {company.workingHours}</div>
            </div>
          </div>

          <div className="bg-zinc-50 p-6 rounded border border-zinc-200/80 space-y-4">
            <h4 className="text-sm font-bold text-zinc-900">Остались вопросы или нужен расчет?</h4>
            <p className="text-xs text-zinc-500 font-medium leading-relaxed">
              Наши инженеры-технологи подберут идеальный декор камня под ваш бюджет и подготовят бесплатную смету за 15
              минут.
            </p>
            <a
              href="https://wa.me/79852272131"
              target="_blank"
              rel="noreferrer"
              className="inline-flex items-center gap-2 px-4 py-2.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider transition-all"
            >
              Написать в WhatsApp
            </a>
          </div>
        </div>
      </SectionLayout>
    </MainLayout>
  );
}