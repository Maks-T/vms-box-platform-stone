import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { X, BookOpen, Phone, Mail, Send, CheckSquare } from 'lucide-react';
import { cn } from '@/shared/lib/utils';
import { Logo } from '@/shared/components/ui/Logo';
import { NavItem, siteConfig } from '@/shared/config/site';

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
      "fixed inset-0 z-[100] bg-white flex flex-col transition-transform duration-300 ease-in-out lg:hidden",
      isOpen ? "translate-x-0" : "translate-x-full pointer-events-none"
    )}>
      <div className="px-6 py-4 border-b border-[#E2E6EA] flex justify-between items-center shrink-0 bg-white">
        <Logo variant="dark-solid" onClick={onClose} />
        <button
          className="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-[#08274D] active:scale-90 transition-all border border-[#E2E6EA]"
          onClick={onClose}
          aria-label="Закрыть меню"
        >
          <X className="w-6 h-6" />
        </button>
      </div>

      <nav className="flex flex-col px-6 py-4 flex-1 overflow-y-auto">
        {items.map((item) => {
          if (item.disabled) {
            return (
              <span key={item.label} className="py-3.5 text-[15px] font-heading font-semibold uppercase text-slate-300 border-b border-slate-100 cursor-not-allowed select-none">
                {item.label}
              </span>
            );
          }

          const isActive = currentPathname === getPathname(item.href);
          const classes = cn(
            "py-3.5 text-[15px] font-heading font-bold uppercase tracking-wider border-b border-slate-100 transition-colors",
            isActive ? "text-[#EF5042]" : "text-[#08274D] hover:text-[#EF5042]"
          );

          if (item.forceRefresh) {
            return (
              <a key={item.label} href={item.href} className={classes}>
                {item.label}
              </a>
            );
          }

          return (
            <Link key={item.label} href={item.href} className={classes} onClick={onClose}>
              {item.label}
            </Link>
          );
        })}

        {/* Блок контактов для мобильного */}
        <div className="mt-8 pt-6 border-t border-[#E2E6EA] flex flex-col gap-4">
          <a
            href={contacts.phone.href}
            className="flex items-center gap-3 font-heading font-bold text-[#08274D] text-base"
          >
            <Phone className="w-4 h-4 text-[#9B6A38]" />
            {contacts.phone.label}
          </a>
          <a
            href={contacts.email.href}
            className="flex items-center gap-3 font-medium text-[#696973] text-sm"
          >
            <Mail className="w-4 h-4 text-[#9B6A38]" />
            {contacts.email.label}
          </a>

          <div className="flex items-center gap-3 mt-2">
            {contacts.telegram && (
              <a
                href={contacts.telegram.href}
                target="_blank"
                rel="noreferrer"
                className="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg bg-[#0B9BF5]/10 text-[#0B9BF5] font-bold text-xs uppercase"
              >
                <Send className="w-4 h-4" /> Telegram
              </a>
            )}
            {contacts.orderCalc && (
              <a
                href={contacts.orderCalc.href}
                target="_blank"
                rel="noreferrer"
                className="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg bg-[#08274D] text-white font-bold text-xs uppercase"
              >
                <CheckSquare className="w-4 h-4" /> Расчет
              </a>
            )}
          </div>
        </div>

        {isDev && (
          <a
            href="/docs/api"
            target="_blank"
            rel="noreferrer"
            className="mt-6 flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-slate-100 text-[#08274D] font-bold text-xs tracking-widest uppercase"
          >
            <BookOpen className="w-5 h-5" />
            Swagger API
          </a>
        )}
      </nav>
    </div>
  );
}
