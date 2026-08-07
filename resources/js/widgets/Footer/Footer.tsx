import React from 'react';
import { Logo } from '@/shared/components/ui/Logo';
import SocialLink from '@/shared/components/ui/SocialLink';
import { siteConfig } from '@/shared/config/site';

export default function Footer() {
  const { socials, company, contacts } = siteConfig;

  return (
    <footer className="w-full bg-[#18181B] text-white pt-12 pb-8 mt-20 border-t border-zinc-800">
      <div className="max-w-[1400px] mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 text-xs font-normal text-zinc-400 leading-relaxed border-b border-zinc-800/80 pb-10">
        <div className="space-y-3">
          <Logo variant="black" imgClassName="h-10 w-auto mb-2" />
          <p className="text-zinc-400 leading-relaxed">
            Производство и профессиональная обработка изделий из искусственного и натурального камня.
          </p>
          <div className="text-[11px] text-zinc-500 pt-1 space-y-0.5">
            <div>ИНН: 7728345210</div>
            <div>ОГРН: 1167746720192</div>
          </div>
        </div>

        <div className="space-y-3">
          <div className="font-bold text-white text-xs uppercase tracking-wider mb-2">
            Каталог
          </div>
          <ul className="space-y-2">
            <li><a href="/?family=stone" className="hover:text-white transition-colors">Акриловый камень</a></li>
            <li><a href="/?family=stone&product_type=quartz_stone" className="hover:text-white transition-colors">Кварцевый агломерат</a></li>
            <li><a href="/?family=sink" className="hover:text-white transition-colors">Кухонные мойки</a></li>
            <li><a href="/?family=mixer" className="hover:text-white transition-colors">Смесители и раковины</a></li>
          </ul>
        </div>

        <div className="space-y-3">
          <div className="font-bold text-white text-xs uppercase tracking-wider mb-2">
            Контакты
          </div>
          <div className="space-y-1.5">
            <div className="text-white font-bold">{contacts.phone.label}</div>
            <div className="text-white font-bold">{contacts.phoneAdd?.label}</div>
            <div className="text-zinc-300">{contacts.email.label}</div>
            <div className="pt-1 text-zinc-500">{contacts.address}</div>
          </div>
        </div>

        <div className="space-y-3">
          <div className="font-bold text-white text-xs uppercase tracking-wider mb-2">
            Мессенджеры и рейтинг
          </div>
          <div className="flex items-center gap-2 mb-3">
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
          <div className="flex items-center gap-2">
            <span className="text-base font-bold text-white">4.8</span>
            <div className="text-amber-400 text-xs">★★★★★</div>
          </div>
          <p className="text-zinc-500">Оценка клиентов по 350+ выполненным проектам.</p>
        </div>
      </div>

      <div className="max-w-[1400px] mx-auto px-6 pt-5 text-[11px] text-zinc-500 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>{company.copyright}</div>
        <div className="text-zinc-500">Производство столешниц в Москве</div>
      </div>
    </footer>
  );
}