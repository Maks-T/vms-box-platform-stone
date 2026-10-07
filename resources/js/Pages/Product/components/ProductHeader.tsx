import React from 'react';
import { Link } from '@inertiajs/react';
import { Share2 } from 'lucide-react';
import { route } from 'ziggy-js';
import { toast } from 'sonner';

interface Props {
  productName?: string;
  categoryName?: string;
}

export function ProductHeader({ productName, categoryName = 'Кварцевый камень' }: Props) {
  const handleShare = () => {
    navigator.clipboard.writeText(window.location.href);
    toast.success('Ссылка на слэб скопирована в буфер обмена');
  };

  return (
    <div className="flex items-center justify-between border-b border-[#E5E5E5] pb-4 mb-6 sm:mb-8 text-left">
      <nav className="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-gray-400">
        <Link href="/" className="hover:text-[#212B36] transition-colors">Главная</Link>
        <span>—</span>
        <Link href={route('catalog')} className="hover:text-[#212B36] transition-colors">Каталог камня</Link>
        <span>—</span>
        <Link href={route('catalog')} className="hover:text-[#212B36] transition-colors">{categoryName}</Link>
        {productName && (
          <>
            <span>—</span>
            <span className="text-gray-600 font-medium">{productName}</span>
          </>
        )}
      </nav>

      <button
        type="button"
        onClick={handleShare}
        className="text-gray-400 hover:text-[#212B36] transition-colors p-1.5 rounded-sm hover:bg-gray-100 cursor-pointer"
        title="Поделиться"
      >
        <Share2 className="w-4 h-4" />
      </button>
    </div>
  );
}

export default ProductHeader;