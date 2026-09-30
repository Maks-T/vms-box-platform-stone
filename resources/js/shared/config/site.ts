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
    name: "МастерСтоун",
    status: "Изделия из камня в СПб",
    tagline: "Производство изделий из натурального и искусственного камня",
    copyright: `© 2010–${new Date().getFullYear()} МастерСтоун. Все права защищены.`,
    address: "г. Санкт-Петербург, Студенческая ул., д. 10, ТЦ «Ланской», 3 эт., С-12",
    productionAddress: "Ленинградская область, г. Гатчина, ул. 120-й Гатчинской Дивизии, 10",
    workHours: "Ежедневно с 10:00 до 20:00",
  },

  contacts: {
    phone: { label: "+7 (812) 243-99-98", href: "tel:+78122439998" },
    email: { label: "info@masterstone-spb.ru", href: "mailto:info@masterstone-spb.ru" },
    telegram: { label: "TELEGRAM", href: "https://t.me/Masterstone_bot" },
    max: { label: "MAX", href: "https://max.ru/id4705049517_bot" },
    whatsapp: { label: "WhatsApp", href: "https://wa.me/79818875667" },
    orderCalc: { label: "Заказать расчет", href: "https://forms.amocrm.ru/rzlrcrm" },
  },

  socials: [
    { id: 'telegram', src: "/images/icons/telegram.svg", href: "https://t.me/Masterstone_bot", label: "Telegram" },
    { id: 'whatsapp', src: "/images/icons/whatsapp.svg", href: "https://wa.me/79818875667", label: "WhatsApp" },
    { id: 'vk', src: "/images/icons/vk.svg", href: "https://vk.com/marble_granite", label: "ВКонтакте" },
  ] as SocialItem[],

  headerNav: [
    { label: 'Каталог камня', href: route('catalog'), disabled: false },
    { label: 'Услуги', href: route('services'), disabled: false },
    { label: 'Калькулятор', href: route('calculator.show'), disabled: false, forceRefresh: true },
    { label: 'Конфигурация', href: route('bootstrap'), disabled: false },
    { label: 'О компании', href: '#', disabled: true },
    { label: 'Контакты', href: '#', disabled: true },
  ] as (NavItem & { forceRefresh?: boolean })[],

};
