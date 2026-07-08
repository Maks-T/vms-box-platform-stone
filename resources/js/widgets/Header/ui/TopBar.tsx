import React from 'react';
import { Phone, Mail } from 'lucide-react';
import StatusBadge from '@/shared/components/ui/StatusBadge';
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
  const { contacts, company } = siteConfig;

  
  const languageOptions: PillOption<string>[] = [
    { value: 'ru', label: 'RU' },
    { value: 'en', label: 'EN' },
  ];

  
  const modeOptions: PillOption<boolean>[] = [
    { value: false, label: 'PROD', title: 'Переключить в обычный пользовательский режим' },
    { value: true, label: 'DEV', title: 'Переключить в режим разработчика' },
  ];

  return (
    <div className="hidden lg:block border-b border-white/5">
      <div className="max-w-[1400px] mx-auto px-4 md:px-8 py-2.5 flex justify-between items-center">
        <div className="flex items-center gap-6">
          <a href={contacts.phone.href} className="flex items-center gap-2 text-white/60 hover:text-white transition-colors text-sm font-medium">
            <Phone className="w-4 h-4 opacity-70" />
            {contacts.phone.label}
          </a>
          <a href={contacts.email.href} className="flex items-center gap-2 text-white/60 hover:text-white transition-colors text-sm font-medium">
            <Mail className="w-4 h-4 opacity-70" />
            {contacts.email.label}
          </a>
        </div>

        <div className="flex items-center gap-6">

          {}
          {(isDev || isEmployee) && (
            <PillSwitcher
              options={modeOptions}
              activeValue={isDev}
              onChange={(val) => setDevMode(val)} 
            />
          )}

          {}
          <PillSwitcher
            options={languageOptions}
            activeValue={locale}
            onChange={(val) => onLanguageChange(val)} 
          />

          {}
          {isDev && (
            <StatusBadge variant="success">
              {company.status}
            </StatusBadge>
          )}
        </div>
      </div>
    </div>
  );
}
