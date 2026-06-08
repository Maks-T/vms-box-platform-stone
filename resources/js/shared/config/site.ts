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
    name: "VMS-NC Box (On-Premise)",
    status: "Тестовая среда",
    copyright: `© ${new Date().getFullYear()} VMS-NC. Все права защищены.`,
  },

  contacts: {
    phone: { label: "+375 29 743 43 17", href: "tel:+375297434317" },
    email: { label: "info@vistegra.by", href: "mailto:info@vistegra.by" },
  },

  socials: [
    { id: 'telegram', src: "/images/icons/telegram.svg", href: "https://t.me/Andrey_Uglikov", label: "Telegram" },
    { id: 'viber', src: "/images/icons/viber.svg", href: "viber://chat?number=+375291898322", label: "Viber" },
    { id: 'whatsapp', src: "/images/icons/whatsapp.svg", href: "https://wa.me/375291898322", label: "WhatsApp" },
    { id: 'chanel', src: "/images/icons/chanel.svg", href: "https://t.me/margin_sense", label: "Канал основателя" },
    {
      id: 'linkedin',
      src: "/images/icons/linkedin.svg",
      href: "https://www.linkedin.com/in/andrey-uglikov-4945881a8/",
      label: "LinkedIn"
    },
  ] as SocialItem[],

  headerNav: [
    { label: 'Конфигурация', href: route('bootstrap'), disabled: false },
    { label: 'Каталог', href: route('catalog'), disabled: false },
    { label: 'Услуги (Матрица)', href: route('services'), disabled: false },
    { label: 'О компании', href: '#', disabled: true },
  ] as NavItem[],

};
