import React from 'react';
import { Link } from '@inertiajs/react';
import { NavItem } from '@/shared/config/site';

export default function NavBar({ items }: { items: NavItem[] }) {
  return (
    <nav className="hidden lg:flex items-center gap-8 h-full">
      {items.map((item) => (
        item.disabled ? (
          <span key={item.label} className="text-white/30 cursor-not-allowed select-none text-[15px] font-medium py-4">
            {item.label}
          </span>
        ) : (
          <Link
            key={item.label}
            href={item.href}
            className="text-white/80 hover:text-white text-[15px] font-medium py-4 relative group transition-colors"
          >
            {item.label}
            <span className="absolute bottom-3 left-0 w-0 h-[2px] bg-primary transition-all duration-300 group-hover:w-full" />
          </Link>
        )
      ))}
    </nav>
  );
}