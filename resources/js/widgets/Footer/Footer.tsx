import React from 'react';
import { Link } from '@inertiajs/react';
import { Logo } from '@/shared/components/ui/Logo';
import { siteConfig } from '@/shared/config/site';
import { Phone, Mail, MapPin, Clock, ArrowUpRight } from 'lucide-react';
import { route } from 'ziggy-js';

export default function Footer() {
  const { company, contacts } = siteConfig;

  return (
    <footer className="w-full bg-[#08274D] text-white pt-16 pb-10 mt-auto border-t border-[#05162B]">
      <div className="max-w-[1440px] mx-auto px-4 md:px-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12 pb-12 border-b border-white/10">
          
          {/* Колонка 1: О компании и лого */}
          <div className="flex flex-col gap-4">
            <Logo variant="light-solid" />
            <p className="text-slate-300 text-sm leading-relaxed mt-2">
              Изготовление столешниц, подоконников, ступеней и каминов из натурального и искусственного камня на заказ в Санкт-Петербурге и Ленинградской области.
            </p>
            <div className="text-xs text-slate-400">
              Опыт работы с камнем с 2010 года. Собственное производство в Гатчине.
            </div>
          </div>

          {/* Колонка 2: Изделия и услуги */}
          <div className="flex flex-col gap-3">
            <h4 className="font-heading font-bold text-white uppercase text-[14px] tracking-wider mb-2">
              Изделия и расчет
            </h4>
            <ul className="flex flex-col gap-2 text-sm text-slate-300">
              <li><Link href={route('calculator.show')} className="hover:text-[#EF5042] transition-colors">Онлайн-калькулятор изделий</Link></li>
              <li><Link href={route('calculator.show')} className="hover:text-[#EF5042] transition-colors">Столешницы для кухни и ванной</Link></li>
              <li><Link href={route('calculator.show')} className="hover:text-[#EF5042] transition-colors">Подоконники и барные стойки</Link></li>
              <li><Link href={route('services')} className="hover:text-[#EF5042] transition-colors">Услуги обработки и распила</Link></li>
              <li><a href={contacts.orderCalc?.href || '#'} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1 text-[#EF5042] hover:underline font-semibold">Заказать расчет по чертежу <ArrowUpRight className="w-3.5 h-3.5" /></a></li>
            </ul>
          </div>

          {/* Колонка 3: Каталог материалов */}
          <div className="flex flex-col gap-3">
            <h4 className="font-heading font-bold text-white uppercase text-[14px] tracking-wider mb-2">
              Каталог камня
            </h4>
            <ul className="flex flex-col gap-2 text-sm text-slate-300">
              <li><Link href={route('catalog')} className="hover:text-[#EF5042] transition-colors font-medium text-white">Все материалы каталога</Link></li>
              <li><Link href={`${route('catalog')}?family=stone&product_type=quartz_stone`} className="hover:text-[#EF5042] transition-colors">Кварцевый агломерат</Link></li>
              <li><Link href={`${route('catalog')}?family=stone&product_type=acrylic_stone`} className="hover:text-[#EF5042] transition-colors">Акриловый камень</Link></li>
              <li><Link href={`${route('catalog')}?family=stone`} className="hover:text-[#EF5042] transition-colors">Натуральный камень (мрамор, гранит)</Link></li>
              <li><Link href={`${route('catalog')}?family=sinks`} className="hover:text-[#EF5042] transition-colors">Мойки и раковины</Link></li>
            </ul>
          </div>

          {/* Колонка 4: Контакты и адреса */}
          <div className="flex flex-col gap-3">
            <h4 className="font-heading font-bold text-white uppercase text-[14px] tracking-wider mb-2">
              Контакты в СПб
            </h4>
            <div className="flex flex-col gap-3 text-sm text-slate-300">
              <a href={contacts.phone.href} className="flex items-center gap-2 font-bold text-white hover:text-[#EF5042] transition-colors">
                <Phone className="w-4 h-4 text-[#9B6A38] shrink-0" />
                {contacts.phone.label}
              </a>
              <a href={contacts.email.href} className="flex items-center gap-2 hover:text-[#EF5042] transition-colors">
                <Mail className="w-4 h-4 text-[#9B6A38] shrink-0" />
                {contacts.email.label}
              </a>
              <div className="flex items-start gap-2 text-xs leading-relaxed text-slate-300">
                <MapPin className="w-4 h-4 text-[#9B6A38] shrink-0 mt-0.5" />
                <span>{company.address}</span>
              </div>
              <div className="flex items-center gap-2 text-xs text-slate-300">
                <Clock className="w-4 h-4 text-[#9B6A38] shrink-0" />
                <span>{company.workHours}</span>
              </div>
            </div>
          </div>

        </div>

        {/* Нижняя полоса */}
        <div className="pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-400">
          <div>
            {company.copyright}
          </div>
          <div className="text-slate-400 text-center md:text-right">
            Цены на сайте носят информационный характер и не являются публичной офертой.
          </div>
        </div>
      </div>
    </footer>
  );
}