import React from 'react';
import { Phone, Mail } from 'lucide-react';
import { cn } from '@/shared/lib/utils';
import StatusBadge from '@/shared/components/ui/StatusBadge';
import { siteConfig } from '@/shared/config/site';

interface TopBarProps {
  locale: string;
  onLanguageChange: (lang: string) => void;
}

export default function TopBar({ locale, onLanguageChange }: TopBarProps) {
  const { contacts, company } = siteConfig;

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
          <div className="flex items-center gap-2 bg-white/5 rounded-full p-1 border border-white/10">
            <button
              onClick={() => onLanguageChange('ru')}
              className={cn("px-3 py-1 rounded-full text-xs font-bold transition-colors cursor-pointer", locale === 'ru' ? "bg-white text-slate-900" : "text-white/60 hover:text-white")}
            >
              RU
            </button>
            <button
              onClick={() => onLanguageChange('en')}
              className={cn("px-3 py-1 rounded-full text-xs font-bold transition-colors cursor-pointer", locale === 'en' ? "bg-white text-slate-900" : "text-white/60 hover:text-white")}
            >
              EN
            </button>
          </div>

          <StatusBadge variant="success">
            {company.status}
          </StatusBadge>
        </div>
      </div>
    </div>
  );
}
