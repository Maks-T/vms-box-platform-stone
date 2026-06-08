import React from 'react';
import { Link } from '@inertiajs/react';
import { X, BookOpen } from 'lucide-react';
import { cn } from '@/shared/lib/utils';
import { Logo } from '@/shared/components/ui/Logo';
import { NavItem } from '@/shared/config/site';

interface MobileMenuProps {
  isOpen: boolean;
  onClose: () => void;
  items: NavItem[];
}

export default function MobileMenu({ isOpen, onClose, items }: MobileMenuProps) {
  return (
    <div className={cn(
      "fixed inset-0 z-[100] bg-[#16191B] flex flex-col transition-transform duration-500 ease-in-out lg:hidden",
      isOpen ? "translate-x-0" : "translate-x-full"
    )}>
      <div className="px-6 py-5 border-b border-white/5 flex justify-between items-center shrink-0">
        <Logo variant="light-solid" onClick={onClose} />
        <button
          className="w-10 h-10 bg-white/5 rounded-lg flex items-center justify-center text-white active:scale-90 transition-all border border-white/10"
          onClick={onClose}
        >
          <X className="w-6 h-6" />
        </button>
      </div>

      <nav className="flex flex-col px-6 py-4 flex-1">
        {items.map((item) => (
          item.disabled ? (
            <span key={item.label} className="py-4 text-[18px] text-white/30 font-medium border-b border-white/5 cursor-not-allowed select-none">
              {item.label}
            </span>
          ) : (
            <Link key={item.label} href={item.href} className="py-4 text-[18px] text-white font-medium border-b border-white/5" onClick={onClose}>
              {item.label}
            </Link>
          )
        ))}

        <a
          href="/docs/api"
          target="_blank"
          rel="noreferrer"
          className="mt-8 flex items-center justify-center gap-2 w-full py-4 rounded-xl bg-primary text-primary-foreground font-bold tracking-widest uppercase"
        >
          <BookOpen className="w-5 h-5" />
          Swagger API
        </a>
      </nav>
    </div>
  );
}