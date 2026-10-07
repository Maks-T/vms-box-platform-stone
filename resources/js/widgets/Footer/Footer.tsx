import React from 'react';
import { Link } from '@inertiajs/react';
import { siteConfig } from '@/shared/config/site';
import { Phone, Mail, MapPin } from 'lucide-react';
import { route } from 'ziggy-js';

export default function Footer() {
  const { company, contacts } = siteConfig;

  return (
    <footer className="mt-auto bg-[#212B36] text-white border-t border-[#2D3A49]">

      {/* Верхняя инфо-полоса футера в стиле Aspro */}
      <div className="border-b border-[#2D3A49] py-8">
        <div className="max-w-[1412px] mx-auto px-4 md:px-8">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-center">

            {/* Телефон */}
            <div className="flex items-center gap-3.5 bg-[#2D3A49] p-3 rounded-md">
              <span className="text-[#25CED1]">
                <Phone className="w-4 h-4" />
              </span>
              <div className="text-left">
                <a href={contacts.phone.href} className="font-bold text-sm tracking-wide text-white hover:text-[#25CED1] transition-colors block">
                  {contacts.phone.label}
                </a>
                <span className="text-[11px] text-gray-400">Пн. – Пт.: с 9:00 до 21:00</span>
              </div>
            </div>

            {/* Email */}
            <div className="flex items-center gap-3 text-left">
              <span className="text-gray-400">
                <Mail className="w-5 h-5 text-gray-400" />
              </span>
              <div>
                <span className="text-xs text-gray-400 block">Электронная почта:</span>
                <a href={contacts.email.href} className="text-sm font-semibold hover:text-[#25CED1] transition-colors">
                  {contacts.email.label}
                </a>
              </div>
            </div>

            {/* Адрес шоурума */}
            <div className="flex items-center gap-3 text-left">
              <span className="text-gray-400">
                <MapPin className="w-5 h-5 text-gray-400 shrink-0" />
              </span>
              <div className="text-xs text-gray-300 leading-snug">
                {company.address}
              </div>
            </div>

            {/* Социальные сети */}
            <div className="flex items-center justify-start lg:justify-end gap-3">
              <a
                href="https://vk.com/izdeliaquartz"
                target="_blank"
                rel="noreferrer"
                className="w-10 h-10 rounded-md bg-[#2D3A49] hover:bg-[#0077FF] flex items-center justify-center transition-colors text-white font-bold text-xs"
                title="ВКонтакте"
              >
                VK
              </a>
              <a
                href="https://www.instagram.com/quartz.master/"
                target="_blank"
                rel="noreferrer"
                className="w-10 h-10 rounded-md bg-[#2D3A49] hover:bg-[#E4405F] flex items-center justify-center transition-colors text-white font-bold text-xs"
                title="Instagram"
              >
                INST
              </a>
            </div>
          </div>
        </div>
      </div>

      {/* Основные 4 колонки ссылок */}
      <div className="py-14 border-b border-[#2D3A49]">
        <div className="max-w-[1412px] mx-auto px-4 md:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 lg:gap-12 text-sm text-left">

            {/* Колонка 1: Изделия */}
            <div>
              <h4 className="font-heading font-bold text-white uppercase tracking-wider text-xs mb-4">
                Изделия из камня
              </h4>
              <ul className="space-y-2.5 text-gray-400">
                <li><Link href={`${route('catalog')}?product_type=countertop`} className="hover:text-[#25CED1] transition-colors">Кухонные столешницы</Link></li>
                <li><Link href={`${route('catalog')}?product_type=sills`} className="hover:text-[#25CED1] transition-colors">Подоконники</Link></li>
                <li><Link href={`${route('catalog')}?product_type=sinks`} className="hover:text-[#25CED1] transition-colors">Мойки из кварца</Link></li>
                <li><Link href={`${route('catalog')}?product_type=vanity`} className="hover:text-[#25CED1] transition-colors">Столешницы в ванную</Link></li>
                <li><Link href={`${route('catalog')}?product_type=stairs`} className="hover:text-[#25CED1] transition-colors">Лестницы и ступени</Link></li>
                <li><Link href={`${route('catalog')}?product_type=reception`} className="hover:text-[#25CED1] transition-colors">Стойки ресепшн</Link></li>
              </ul>
            </div>

            {/* Колонка 2: Каталог камня */}
            <div>
              <h4 className="font-heading font-bold text-white uppercase tracking-wider text-xs mb-4">
                Каталог камня
              </h4>
              <ul className="space-y-2.5 text-gray-400">
                <li><Link href={`${route('catalog')}?family=stone`} className="hover:text-[#25CED1] transition-colors font-medium text-white">Кварцевый агломерат</Link></li>
                <li><Link href={`${route('catalog')}?family=stone&product_type=ceramics`} className="hover:text-[#25CED1] transition-colors">Широкоформатная керамика</Link></li>
                <li><Link href={route('catalog')} className="hover:text-[#25CED1] transition-colors">Бренды камня</Link></li>
                <li><Link href="/projects/" className="hover:text-[#25CED1] transition-colors">Галерея реализованных работ</Link></li>
              </ul>
            </div>

            {/* Колонка 3: Компания */}
            <div>
              <h4 className="font-heading font-bold text-white uppercase tracking-wider text-xs mb-4">
                О компании
              </h4>
              <ul className="space-y-2.5 text-gray-400">
                <li><Link href="/company/" className="hover:text-[#25CED1] transition-colors">О производстве QuartzMaster</Link></li>
                <li><Link href={route('services')} className="hover:text-[#25CED1] transition-colors">Услуги и замер</Link></li>
                <li><Link href="/company/advantages/" className="hover:text-[#25CED1] transition-colors">Наши преимущества</Link></li>
                <li><Link href="/reviews/" className="hover:text-[#25CED1] transition-colors">Отзывы клиентов</Link></li>
                <li><Link href="/partners/" className="hover:text-[#25CED1] transition-colors">Дизайнерам и партнерам</Link></li>
              </ul>
            </div>

            {/* Колонка 4: Калькулятор и сервис */}
            <div>
              <h4 className="font-heading font-bold text-white uppercase tracking-wider text-xs mb-4">
                Калькулятор и сервис
              </h4>
              <ul className="space-y-2.5 text-gray-400">
                <li><Link href={route('calculator.show')} className="hover:text-[#25CED1] transition-colors text-white font-medium">On-line дизайнер интерьера</Link></li>
                <li><Link href={route('services')} className="hover:text-[#25CED1] transition-colors">Цены и прайс-лист</Link></li>
                <li><Link href="/about-quartz/" className="hover:text-[#25CED1] transition-colors">Уход и эксплуатация</Link></li>
                <li><Link href="#contacts" className="hover:text-[#25CED1] transition-colors">Контакты и адрес офиса</Link></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      {/* Нижний ряд с копирайтом и платежными системами */}
      <div className="py-6 text-xs text-gray-400">
        <div className="max-w-[1412px] mx-auto px-4 md:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-6">
            <p>{company.copyright}</p>
            <a href="/privacy/" className="hover:text-white transition-colors underline">
              Политика конфиденциальности
            </a>
          </div>

          {/* Платежные системы */}
          <div className="flex items-center gap-2">
            <span className="px-2 py-0.5 bg-[#2D3A49] rounded-xs text-[10px] font-semibold text-gray-300">MIR</span>
            <span className="px-2 py-0.5 bg-[#2D3A49] rounded-xs text-[10px] font-semibold text-gray-300">VISA</span>
            <span className="px-2 py-0.5 bg-[#2D3A49] rounded-xs text-[10px] font-semibold text-gray-300">MASTERCARD</span>
            <span className="px-2 py-0.5 bg-[#2D3A49] rounded-xs text-[10px] font-semibold text-gray-300">SBER</span>
            <span className="px-2 py-0.5 bg-[#2D3A49] rounded-xs text-[10px] font-semibold text-gray-300">T-BANK</span>
          </div>
        </div>
      </div>
    </footer>
  );
}