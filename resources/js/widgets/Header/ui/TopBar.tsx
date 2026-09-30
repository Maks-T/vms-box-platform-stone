import React from 'react';
import { Phone, Mail, Send, MessageSquare, CheckSquare } from 'lucide-react';
import { siteConfig } from '@/shared/config/site';
import { setDevMode } from '@/shared/lib/dev';

import PillSwitcher, { PillOption } from '@/shared/components/ui/PillSwitcher';

interface TopBarProps {
  locale: string;
  onLanguageChange: (lang: string) => void;
  isDev: boolean;
  isEmployee: boolean; 
}

export default function TopBar({ locale, onLanguageChange, isDev, isEmployee }: TopBarProps) {
  const { contacts } = siteConfig;

  
  const languageOptions: PillOption<string>[] = [
    { value: 'ru', label: 'RU' },
    { value: 'en', label: 'EN' },
  ];

  
  const modeOptions: PillOption<boolean>[] = [
    { value: false, label: 'PROD', title: 'Переключить в обычный пользовательский режим' },
    { value: true, label: 'DEV', title: 'Переключить в режим разработчика' },
  ];

  return (
    <div className="hidden lg:block bg-white border-b border-[#E2E6EA] text-[13px]">
      <div className="max-w-[1440px] mx-auto px-4 md:px-8 py-2 flex justify-between items-center">
        {/* Левая колонка: контакты СПб */}
        <div className="flex items-center gap-6 font-heading">
          <a
            href={contacts.email.href}
            className="flex items-center gap-2 font-bold text-[#08274D] hover:text-[#EF5042] uppercase tracking-wide transition-colors"
          >
            <Mail className="w-3.5 h-3.5 text-[#9B6A38]" />
            {contacts.email.label}
          </a>
          <a
            href={contacts.phone.href}
            className="flex items-center gap-2 font-bold text-[#08274D] hover:text-[#EF5042] tracking-wide transition-colors"
          >
            <Phone className="w-3.5 h-3.5 text-[#9B6A38]" />
            {contacts.phone.label}
          </a>
        </div>

        {/* Правая колонка: мессенджеры и заказ расчета */}
        <div className="flex items-center gap-5">
          {contacts.telegram && (
            <a
              href={contacts.telegram.href}
              target="_blank"
              rel="noreferrer"
              className="flex items-center gap-1.5 font-heading font-extrabold text-[12px] text-[#0B9BF5] hover:opacity-80 uppercase tracking-wider transition-opacity"
            >
              <Send className="w-3.5 h-3.5" />
              {contacts.telegram.label}
            </a>
          )}

          {contacts.max && (
            <a
              href={contacts.max.href}
              target="_blank"
              rel="noreferrer"
              className="flex items-center gap-1.5 font-heading font-extrabold text-[12px] text-[#1943EF] hover:opacity-80 uppercase tracking-wider transition-opacity"
            >
              <MessageSquare className="w-3.5 h-3.5" />
              {contacts.max.label}
            </a>
          )}

          {contacts.orderCalc && (
            <a
              href={contacts.orderCalc.href}
              target="_blank"
              rel="noreferrer"
              className="flex items-center gap-1.5 font-heading font-bold text-[12px] text-[#08274D] hover:text-[#EF5042] uppercase tracking-wider transition-colors ml-1"
            >
              <CheckSquare className="w-4 h-4 text-[#08274D]" />
              {contacts.orderCalc.label}
            </a>
          )}

          {(isDev || isEmployee) && (
            <div className="h-4 w-[1px] bg-[#E2E6EA] mx-1" />
          )}

          {(isDev || isEmployee) && (
            <PillSwitcher
              options={modeOptions}
              activeValue={isDev}
              onChange={(val) => setDevMode(val)}
            />
          )}

          <PillSwitcher
            options={languageOptions}
            activeValue={locale}
            onChange={(val) => onLanguageChange(val)}
          />
        </div>
      </div>
    </div>
  );
}
