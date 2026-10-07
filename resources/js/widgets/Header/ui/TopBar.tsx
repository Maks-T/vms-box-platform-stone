import React, { useState } from 'react';
import { Phone, Search, ChevronDown, Mail, MapPin } from 'lucide-react';
import { siteConfig } from '@/shared/config/site';
import { setDevMode } from '@/shared/lib/dev';
import { Logo } from '@/shared/components/ui/Logo';
import PillSwitcher, { PillOption } from '@/shared/components/ui/PillSwitcher';
import { Link, router } from '@inertiajs/react';
import { route } from 'ziggy-js';

interface TopBarProps {
  locale: string;
  onLanguageChange: (lang: string) => void;
  isDev: boolean;
  isEmployee: boolean;
}

export default function TopBar({ locale, onLanguageChange, isDev, isEmployee }: TopBarProps) {
  const { contacts } = siteConfig;
  const [searchQuery, setSearchQuery] = useState('');

  const languageOptions: PillOption<string>[] = [
    { value: 'ru', label: 'RU' },
    { value: 'en', label: 'EN' },
  ];

  const modeOptions: PillOption<boolean>[] = [
    { value: false, label: 'PROD', title: 'Переключить в обычный пользовательский режим' },
    { value: true, label: 'DEV', title: 'Переключить в режим разработчика' },
  ];

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      router.visit(`${route('catalog')}?search=${encodeURIComponent(searchQuery.trim())}`);
    }
  };

  return (
    <div className="bg-[#212B36] text-white border-b border-[#2D3A49]">
      <div className="max-w-[1412px] mx-auto px-4 md:px-8 h-[76px] lg:h-[84px] flex items-center justify-between gap-4 md:gap-8">

        {/* Логотип */}
        <div className="flex items-center gap-4 shrink-0">
          <Logo />
        </div>

        {/* Строка поиска по названию камня */}
        <div className="hidden md:flex flex-1 max-w-[460px]">
          <form onSubmit={handleSearch} className="relative w-full">
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Поиск по названию камня"
              className="w-full h-[42px] bg-[#2D3A49] text-white placeholder-gray-400 text-sm pl-4 pr-12 rounded-md border border-[#3E4E5E] focus:border-[#25CED1] focus:outline-none transition-colors"
            />
            <button
              type="submit"
              className="absolute right-0 top-0 h-[42px] w-[42px] flex items-center justify-center text-gray-400 hover:text-[#25CED1] transition-colors cursor-pointer"
              title="Найти"
            >
              <Search className="w-4 h-4" />
            </button>
          </form>
        </div>

        {/* Контакты и кнопка "Заказать расчет" */}
        <div className="flex items-center gap-4 lg:gap-6 shrink-0">

          {/* Выпадающий блок телефона */}
          <div className="relative group">
            <div className="flex items-center gap-2 cursor-pointer py-2">
              <span className="w-8 h-8 rounded-md bg-[#2D3A49] flex items-center justify-center text-[#25CED1]">
                <Phone className="w-4 h-4" />
              </span>
              <div className="hidden sm:block text-left">
                <a href={contacts.phone.href} className="font-semibold text-sm lg:text-[15px] text-white hover:text-[#25CED1] transition-colors whitespace-nowrap block">
                  {contacts.phone.label}
                </a>
                <span className="text-[11px] text-gray-400 block -mt-0.5">Пн–Пт: 9:00–21:00</span>
              </div>
              <ChevronDown className="w-3 h-3 text-gray-400 group-hover:text-white transition-transform group-hover:rotate-180" />
            </div>

            {/* Dropdown контактов офиса и шоурума */}
            <div className="absolute right-0 top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 w-[290px] z-50">
              <div className="bg-[#2D3A49] border border-[#3E4E5E] rounded-md p-5 shadow-2xl text-sm">
                <div className="mb-4 text-left">
                  <div className="text-[11px] uppercase tracking-wider text-gray-400 mb-1 flex items-center gap-1.5">
                    <Mail className="w-3 h-3 text-[#25CED1]" /> E-mail:
                  </div>
                  <a href={contacts.email.href} className="text-white hover:text-[#25CED1] font-medium transition-colors text-xs">
                    {contacts.email.label}
                  </a>
                </div>
                <div className="mb-4 text-left">
                  <div className="text-[11px] uppercase tracking-wider text-gray-400 mb-1 flex items-center gap-1.5">
                    <MapPin className="w-3 h-3 text-[#25CED1]" /> Офис & Шоурум:
                  </div>
                  <p className="text-gray-200 text-xs leading-relaxed">
                    129164, г. Москва, ул. Ярославская, д. 8, к. 5, офис 405
                  </p>
                </div>
                <div className="pt-3 border-t border-[#3E4E5E]">
                  <Link
                    href={route('calculator.show')}
                    className="w-full py-2 bg-transparent border border-[#25CED1] text-[#25CED1] hover:bg-[#25CED1] hover:text-[#212B36] rounded-md text-xs font-semibold uppercase tracking-wider transition-colors block text-center"
                  >
                    Заказать звонок
                  </Link>
                </div>
              </div>
            </div>
          </div>

          {/* Фирменный красный CTA #ED1C24 */}
          <Link
            href={route('calculator.show')}
            className="bg-[#ED1C24] hover:bg-white hover:text-[#212B36] text-white px-5 py-2.5 rounded-md font-heading font-bold text-xs uppercase tracking-wider transition-all duration-200 shadow-sm shrink-0 hidden sm:inline-block"
          >
            Заказать расчет
          </Link>

          {(isDev || isEmployee) && (
            <PillSwitcher
              options={modeOptions}
              activeValue={isDev}
              onChange={(val) => setDevMode(val)}
              className="hidden xl:inline-flex !bg-white/10 !border-white/10 !text-white"
            />
          )}

          <PillSwitcher
            options={languageOptions}
            activeValue={locale}
            onChange={(val) => onLanguageChange(val)}
            className="hidden sm:inline-flex !bg-white/10 !border-white/10 !text-white"
          />
        </div>
      </div>
    </div>
  );
}