import { route } from 'ziggy-js';

export interface NavItem {
  label: string;
  href: string;
  disabled?: boolean;
}

export interface SocialItem {
  id: string;
  src?: string;
  icon?: any;
  href: string;
  label: string;
}

export const siteConfig = {
  company: {
    name: "QuartzMaster",
    status: "Шоурум в Москве",
    tagline: "Изделия из кварца и широкоформатной керамики",
    copyright: `© 2008 – ${new Date().getFullYear()} QuartzMaster изделия из кварца`,
    address: "129164, г. Москва, ул. Ярославская, д. 8, к. 5, офис 405",
    workHours: "Пн–Пт: 9:00–21:00",
  },

  contacts: {
    phone: { label: "+7 (495) 565 31 66", href: "tel:+74955653166" },
    email: { label: "info@quartz-master.com", href: "mailto:info@quartz-master.com" },
    telegram: { label: "Telegram", href: "https://t.me/quartz_master" },
    whatsapp: { label: "WhatsApp", href: "https://wa.me/74955653166" },
    orderCalc: { label: "Заказать расчет", href: route('calculator.show') },
  },

  socials: [
    { id: 'vk', src: "/images/icons/vk.svg", href: "https://vk.com/izdeliaquartz", label: "ВКонтакте" },
    { id: 'inst', src: "/images/icons/inst.svg", href: "https://www.instagram.com/quartz.master/", label: "Instagram" },
  ] as SocialItem[],

  headerNav: [
    { label: 'Каталог камня', href: route('catalog'), disabled: false },
    { label: 'Калькулятор', href: route('calculator.show'), disabled: false, forceRefresh: true },
    { label: 'О компании', href: '#', disabled: true },
    { label: 'Контакты', href: '#', disabled: true },
  ] as (NavItem & { forceRefresh?: boolean })[],

};
