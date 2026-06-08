import React from 'react';
import { Logo } from '@/shared/components/ui/Logo';
import SocialLink from '@/shared/components/ui/SocialLink';
import { siteConfig } from '@/shared/config/site';

export default function Footer() {
  const { socials, company } = siteConfig;

  return (
    <footer className="w-full bg-[#16191B] text-white pt-12 pb-8 mt-auto border-t border-white/5">
      <div className="max-w-[1400px] mx-auto px-4 md:px-8 flex flex-col md:flex-row justify-between items-center gap-6">

        <div className="flex items-center gap-3 shrink-0">
          <Logo variant="dark-outline" />
        </div>

        <div className="flex items-center gap-3">
          {socials.map((social) => (
            <SocialLink
              key={social.id}
              href={social.href}
              src={social.src}
              size="sm"
              aria-label={social.label}
            />
          ))}
        </div>

        <div className="text-white/40 text-[13px] font-medium tracking-wide text-center md:text-right shrink-0">
          {company.copyright}
        </div>

      </div>
    </footer>
  );
}