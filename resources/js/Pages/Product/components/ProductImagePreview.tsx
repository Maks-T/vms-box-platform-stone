import React from 'react';
import { Image as ImageIcon } from 'lucide-react';

interface Props {
  image: string | null;
  name: string;
  externalCode: string | null;
  sku?: string | null;
  id: number;
}

export function ProductImagePreview({ image, name, externalCode, sku, id }: Props) {
  return (
    <div className="relative aspect-square rounded border border-zinc-200 overflow-hidden flex items-center justify-center p-8 bg-zinc-50/50">
      {image ? (
        <img
          src={image}
          alt={name}
          className="w-full h-full object-contain transition-transform duration-300 hover:scale-105"
        />
      ) : (
        <div className="flex flex-col items-center text-zinc-400">
          <ImageIcon className="w-16 h-16 mb-2" />
          <span className="text-xs font-semibold uppercase tracking-wider text-zinc-400">Нет фото</span>
        </div>
      )}

      <div className="absolute top-4 left-4 bg-zinc-900 text-white text-[9px] font-bold px-2 py-0.5 rounded-sm uppercase tracking-wider">
        Арт: {sku || externalCode || id}
      </div>
    </div>
  );
}