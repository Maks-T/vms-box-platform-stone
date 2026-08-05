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
    name: "Столешка Ру",
    status: "Столешка.Ру - Производство",
    tagline: "Производство столешниц из искусственного и кварцевого камня в Москве",
    copyright: `© 2008–${new Date().getFullYear()} Столешка Ру. Все права защищены.`,
    workingHours: "Пн-Пт 10:00–18:00",
  },

  contacts: {
    phone: { label: "+7 985 227 21 31", href: "tel:+79852272131" },
    phoneAdd: { label: "+7 495 227 21 31", href: "tel:+74952272131" },
    email: { label: "info@stoleshka.ru", href: "mailto:info@stoleshka.ru" },
    address: "г. Москва, ул. Намёткина, д. 10Б",
  },

  socials: [
    { id: 'telegram', src: "/images/icons/telegram.svg", href: "https://t.me/Telegram_stoleshka", label: "Telegram" },
    { id: 'whatsapp', src: "/images/icons/whatsapp.svg", href: "https://wa.me/79852272131", label: "WhatsApp" },
  ] as SocialItem[],

  headerNav: [
    { label: 'Калькулятор', href: route('calculator.show'), disabled: false, forceRefresh: true },
    { label: 'Конфигурация', href: route('bootstrap'), disabled: false },
    { label: 'Каталог', href: route('catalog'), disabled: false },
    { label: 'Услуги', href: route('services'), disabled: false },
    { label: 'О магазине', href: route('about'), disabled: false },
  ] as (NavItem & { forceRefresh?: boolean })[],
};