import React, { useState, useEffect } from 'react';
import { Image as ImageIcon } from 'lucide-react';
import { cn } from '@/shared/lib/utils';

interface Props {
  image: string | null;
  name: string;
  externalCode: string | null;
  id: number;
}

export function ProductImagePreview({ image, name, externalCode, id }: Props) {
  const [activeImage, setActiveImage] = useState<string | null>(image);

  useEffect(() => {
    setActiveImage(image);
  }, [image]);

  const slabCode = externalCode || `R${id}`;

  // Определение типа: слэб (object-cover) или оборудование/сантехника (object-contain)
  const isSlab = (() => {
    const nameLower = (name || '').toLowerCase();
    const codeLower = (externalCode || '').toLowerCase();

    const nonSlabKeywords = [
      'смесител', 'мойк', 'раковина', 'дозатор', 'сифон', 'кран',
      'труб', 'опор', 'сва', 'кляймер', 'крепеж', 'профил', 'поддон',
      'faucet', 'sink', 'tap', 'dispenser', 'level', 'blanco', 'omoikiri'
    ];

    if (nonSlabKeywords.some((kw) => nameLower.includes(kw) || codeLower.includes(kw))) {
      return false;
    }

    return true;
  })();

  const gallery = [
    image || 'https://quartz-master.com/upload/iblock/55f/nr48c75o2a8xqgurhg0pgbth27lrsa9w.jpg',
    'https://quartz-master.com/upload/resize_cache/iblock/90b/1500_1500_0/17fi2dxhwg3rajy66ppv0vlh6uanxj1w.jpg',
    'https://quartz-master.com/upload/resize_cache/iblock/7a2/1500_1500_0/y324a47woim0n24nurhv1ym86za9ql6r.jpg',
    'https://quartz-master.com/upload/resize_cache/iblock/587/1500_1500_0/3x8z52qip0kn5in15sc8f28lke81gsj4.jpg',
    'https://quartz-master.com/upload/resize_cache/iblock/555/1500_1500_0/hpfipwh031z3k9hv12s5cj6mykz1e7zj.jpg',
    'https://quartz-master.com/upload/iblock/a6e/ir7jmf4vbjakwo7hp6ngvlpd82hjp3bo.jpeg',
  ].filter(Boolean);

  return (
    <div className="space-y-6 text-left">
      {/* Главное фото слэба */}
      <div className="relative border border-[#E5E5E5] rounded-md bg-white overflow-hidden shadow-2xs group aspect-square flex items-center justify-center">
        {activeImage ? (
          <img
            src={activeImage}
            alt={name}
            className={cn(
              "w-full h-full transition-transform duration-300 group-hover:scale-105",
              isSlab ? "object-cover" : "object-contain p-6 mix-blend-multiply"
            )}
          />
        ) : (
          <div className="flex flex-col items-center justify-center w-full h-full text-slate-300">
            <ImageIcon className="w-16 h-16 mb-2" />
            <span className="text-xs uppercase tracking-wider">Нет фото</span>
          </div>
        )}

        {/* Фирменный шильдик на слэбе QuartzMaster */}
        <span className="absolute bottom-3 left-3 px-2 py-1 bg-white/95 text-[11px] font-mono text-gray-700 rounded-xs shadow-xs border border-gray-200">
          {slabCode}
        </span>
      </div>

      {/* Фотогалерея слэба из 6 миниатюр */}
      <div className="space-y-3">
        <div className="flex items-center justify-between border-b border-[#E5E5E5] pb-2 text-xs text-gray-500">
          <span className="font-heading font-bold text-sm text-[#212B36]">Фотогалерея</span>
          <span>{gallery.length} фото</span>
        </div>

        <div className="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
          {gallery.map((imgUrl, idx) => (
            <button
              key={idx}
              type="button"
              onClick={() => setActiveImage(imgUrl)}
              className={cn(
                "border rounded-md bg-white overflow-hidden aspect-square transition-all cursor-pointer",
                activeImage === imgUrl ? "border-[#25CED1] ring-2 ring-[#25CED1]/40" : "border-[#E5E5E5] hover:border-gray-400"
              )}
            >
              <img
                src={imgUrl}
                alt={`${name} ракурс ${idx + 1}`}
                className={cn(
                  "w-full h-full",
                  isSlab ? "object-cover" : "object-contain p-1 mix-blend-multiply"
                )}
              />
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}

export default ProductImagePreview;