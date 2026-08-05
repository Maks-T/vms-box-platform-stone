import React from 'react';
import { Phone, Mail } from 'lucide-react';
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
    <div className="hidden lg:block border-b border-border bg-[#F7F8FA] text-zinc-500">
      <div className="max-w-[1400px] mx-auto px-4 md:px-8 py-2 flex justify-between items-center text-xs">
        <div className="flex items-center gap-6">
          <a
            href={contacts.phone.href}
            className="flex items-center gap-2 text-zinc-600 hover:text-zinc-900 transition-colors font-medium"
          >
            <Phone className="w-3.5 h-3.5 opacity-70" />
            {contacts.phone.label}
          </a>
          <a
            href={contacts.email.href}
            className="flex items-center gap-2 text-zinc-600 hover:text-zinc-900 transition-colors font-medium"
          >
            <Mail className="w-3.5 h-3.5 opacity-70" />
            {contacts.email.label}
          </a>
        </div>

        <div className="flex items-center gap-4">
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

          <div className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-white border border-zinc-200 text-zinc-700 text-[11px] font-bold uppercase tracking-wider shadow-xs">
            <span className="w-1.5 h-1.5 rounded-full bg-[#B92B3A]" />
            Шоурум и склад в Москве
          </div>
        </div>
      </div>
    </div>
  );
}