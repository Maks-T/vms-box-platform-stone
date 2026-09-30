import React from 'react';
import { Logo } from '@/shared/components/ui/Logo';
import { siteConfig } from '@/shared/config/site';
import { Phone, Mail, MapPin, Clock } from 'lucide-react';

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

          {/* Колонка 2: Изделия */}
          <div className="flex flex-col gap-3">
            <h4 className="font-heading font-bold text-white uppercase text-[14px] tracking-wider mb-2">
              Изделия из камня
            </h4>
            <ul className="flex flex-col gap-2 text-sm text-slate-300">
              <li><a href="/catalog?product_type=countertop" className="hover:text-[#EF5042] transition-colors">Столешницы для кухни и ванной</a></li>
              <li><a href="/catalog?product_type=sills" className="hover:text-[#EF5042] transition-colors">Подоконники из камня</a></li>
              <li><a href="/catalog?product_type=stairs" className="hover:text-[#EF5042] transition-colors">Лестницы и ступени</a></li>
              <li><a href="/catalog?product_type=fireplaces" className="hover:text-[#EF5042] transition-colors">Камины и порталы</a></li>
              <li><a href="/catalog?product_type=reception" className="hover:text-[#EF5042] transition-colors">Стойки ресепшн и барные зоны</a></li>
            </ul>
          </div>

          {/* Колонка 3: Материалы */}
          <div className="flex flex-col gap-3">
            <h4 className="font-heading font-bold text-white uppercase text-[14px] tracking-wider mb-2">
              Материалы
            </h4>
            <ul className="flex flex-col gap-2 text-sm text-slate-300">
              <li><a href="/catalog?family=stone&product_type=marble" className="hover:text-[#EF5042] transition-colors">Мрамор</a></li>
              <li><a href="/catalog?family=stone&product_type=granite" className="hover:text-[#EF5042] transition-colors">Гранит</a></li>
              <li><a href="/catalog?family=stone&product_type=quartzite" className="hover:text-[#EF5042] transition-colors">Кварцит</a></li>
              <li><a href="/catalog?family=stone&product_type=onyx" className="hover:text-[#EF5042] transition-colors">Оникс</a></li>
              <li><a href="/catalog?family=agglomerate" className="hover:text-[#EF5042] transition-colors">Кварцевый агломерат</a></li>
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