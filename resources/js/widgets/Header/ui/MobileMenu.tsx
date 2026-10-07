import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { X, Phone, Mail, MapPin, Search, Calculator } from 'lucide-react';
import { cn } from '@/shared/lib/utils';
import { Logo } from '@/shared/components/ui/Logo';
import { NavItem, siteConfig } from '@/shared/config/site';
import { route } from 'ziggy-js';

interface ExtendedNavItem extends NavItem {
  forceRefresh?: boolean;
}

interface MobileMenuProps {
  isOpen: boolean;
  onClose: () => void;
  items: ExtendedNavItem[];
  isDev: boolean;
}

export default function MobileMenu({ isOpen, onClose, items, isDev }: MobileMenuProps) {
  const { contacts } = siteConfig;
  const [query, setQuery] = useState('');

  const { url } = usePage();
  const currentPathname = url.split('?')[0];

  const getPathname = (urlStr: string) => {
    if (!urlStr || urlStr.startsWith('#')) return '';
    try {
      const parsed = new URL(urlStr, window.location.origin);
      return parsed.pathname;
    } catch {
      return urlStr.split('?')[0];
    }
  };

  return (
    <div className={cn(
      "fixed inset-0 z-[100] bg-[#212B36] text-white flex flex-col transition-transform duration-300 ease-in-out lg:hidden",
      isOpen ? "translate-x-0" : "translate-x-full pointer-events-none"
    )}>
      <div className="px-6 py-4 border-b border-[#2D3A49] flex justify-between items-center shrink-0 bg-[#212B36]">
        <Logo onClick={onClose} />
        <button
          className="w-10 h-10 rounded-md bg-[#2D3A49] flex items-center justify-center text-white active:scale-90 transition-all border border-[#3E4E5E] cursor-pointer"
          onClick={onClose}
          aria-label="Закрыть меню"
        >
          <X className="w-5 h-5 text-gray-300" />
        </button>
      </div>

      <nav className="flex flex-col px-6 py-4 flex-1 overflow-y-auto">
        {/* Мобильный поиск */}
        <div className="mb-6">
          <form
            onSubmit={(e) => {
              e.preventDefault();
              if (query.trim()) {
                onClose();
                window.location.href = `${route('catalog')}?search=${encodeURIComponent(query.trim())}`;
              }
            }}
            className="relative"
          >
            <input
              type="text"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Поиск по названию камня"
              className="w-full h-10 bg-[#2D3A49] text-white placeholder-gray-400 text-xs pl-3.5 pr-10 rounded-md border border-[#3E4E5E] focus:border-[#25CED1] focus:outline-none"
            />
            <button type="submit" className="absolute right-0 top-0 h-10 w-10 flex items-center justify-center text-gray-400">
              <Search className="w-4 h-4" />
            </button>
          </form>
        </div>

        {/* Ссылки навигации */}
        <div className="flex flex-col text-sm font-semibold uppercase tracking-wider">
          {items.map((item) => {
            if (item.disabled) {
              return (
                <span
                  key={item.label}
                  className="py-3 border-b border-[#2D3A49] text-gray-500/50 cursor-not-allowed select-none"
                >
                  {item.label}
                </span>
              );
            }

            const isActive = currentPathname === getPathname(item.href);

            return (
              <Link
                key={item.label}
                href={item.href}
                onClick={onClose}
                className={cn(
                  "py-3 border-b border-[#2D3A49] transition-colors",
                  isActive ? "text-[#25CED1] font-bold" : "hover:text-[#25CED1]"
                )}
              >
                {item.label}
              </Link>
            );
          })}
        </div>

        {/* Контакты офиса и шоурума */}
        <div className="mt-8 pt-6 border-t border-[#2D3A49] flex flex-col gap-3 text-xs text-gray-300">
          <a
            href={contacts.phone.href}
            className="flex items-center gap-2.5 font-bold text-white text-base hover:text-[#25CED1]"
          >
            <Phone className="w-4 h-4 text-[#25CED1]" />
            {contacts.phone.label}
          </a>
          <a
            href={contacts.email.href}
            className="flex items-center gap-2.5 text-gray-400 hover:text-white"
          >
            <Mail className="w-4 h-4 text-[#25CED1]" />
            {contacts.email.label}
          </a>
          <div className="flex items-start gap-2.5 text-gray-400 leading-relaxed pt-1">
            <MapPin className="w-4 h-4 text-[#25CED1] shrink-0 mt-0.5" />
            <span>129164, г. Москва, ул. Ярославская, д. 8, к. 5, офис 405</span>
          </div>
        </div>

        <div className="mt-8">
          <Link
            href={route('calculator.show')}
            onClick={onClose}
            className="w-full py-3 bg-[#ED1C24] hover:bg-white hover:text-[#212B36] text-white font-heading font-bold text-xs uppercase tracking-wider rounded-md transition-colors block text-center shadow-md"
          >
            Заказать расчет
          </Link>
        </div>
      </nav>
    </div>
  );
}