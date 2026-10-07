import React from 'react';
import { EavAttribute } from '@/types/catalog';
import { AttributeValue } from './AttributeValue';

interface Props {
  attributes: Record<string, EavAttribute>;
  productName?: string;
}

export function ProductAttributes({ attributes, productName }: Props) {
  const entries = Object.entries(attributes || {});

  return (
    <section id="characteristics" className="space-y-5 text-left pt-6">
      <h2 className="font-heading font-bold text-xl sm:text-2xl text-[#212B36]">
        Характеристики {productName || 'камня'}
      </h2>

      <div className="border border-[#E5E5E5] rounded-md bg-white p-6 sm:p-8 shadow-2xs">
        {entries.length > 0 ? (
          <div className="divide-y divide-gray-100 text-sm">
            {entries.map(([code, attr]) => (
              <div key={code} className="py-3 flex items-center justify-between gap-4">
                <span className="text-gray-500 shrink-0">{attr.name}</span>
                <div className="w-full border-b border-dotted border-gray-300 mx-2" />
                <span className="font-semibold text-[#212B36] shrink-0 text-right">
                  <AttributeValue attribute={attr} />
                </span>
              </div>
            ))}
          </div>
        ) : (
          <div className="divide-y divide-gray-100 text-sm">
            <div className="py-3 flex items-center justify-between gap-4">
              <span className="text-gray-500 shrink-0">Материал</span>
              <div className="w-full border-b border-dotted border-gray-300 mx-2" />
              <span className="font-semibold text-[#212B36] shrink-0">Кварцевый агломерат</span>
            </div>
            <div className="py-3 flex items-center justify-between gap-4">
              <span className="text-gray-500 shrink-0">Страна</span>
              <div className="w-full border-b border-dotted border-gray-300 mx-2" />
              <span className="font-semibold text-[#212B36] shrink-0">Россия</span>
            </div>
            <div className="py-3 flex items-center justify-between gap-4">
              <span className="text-gray-500 shrink-0">Толщина камня</span>
              <div className="w-full border-b border-dotted border-gray-300 mx-2" />
              <span className="font-semibold text-[#212B36] shrink-0">20 мм</span>
            </div>
            <div className="py-3 flex items-center justify-between gap-4">
              <span className="text-gray-500 shrink-0">Фактура</span>
              <div className="w-full border-b border-dotted border-gray-300 mx-2" />
              <span className="font-semibold text-[#212B36] shrink-0">Глянцевая</span>
            </div>
          </div>
        )}
      </div>
    </section>
  );
}

export default ProductAttributes;