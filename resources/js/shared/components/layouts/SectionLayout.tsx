import React, {ReactNode, HTMLAttributes} from 'react';
import {cn} from '@/shared/lib/utils';
import BaseContainer from './BaseContainer';

interface SectionLayoutProps extends Omit<HTMLAttributes<HTMLElement>, 'id'> {
  id?: string;
  bg?: string;
  bgElement?: ReactNode;
  extraContent?: ReactNode;
  containerClassName?: string;
  noPadding?: boolean;
  containerVariant?: 'content' | 'page' | 'none';
}

export default function SectionLayout({
                                        children,
                                        bg = "bg-transparent",
                                        bgElement,
                                        extraContent,
                                        className,
                                        containerClassName,
                                        id,
                                        noPadding = false,
                                        containerVariant = 'content',
                                        ...props
                                      }: SectionLayoutProps) {
  const hasBackground = bg !== "bg-transparent";

  if (hasBackground) {
    return (
      <section id={id} className={cn("w-full relative z-0 py-3 md:py-5", className)} {...props}>
        <BaseContainer variant="page" className="relative">

          <div className={cn(
            "relative w-full overflow-hidden rounded-[24px] lg:rounded-[32px] border border-white/5 shadow-2xl",
            !noPadding && "pt-12 md:pt-20 pb-16 md:pb-24 px-4 md:px-10",
            bg
          )}>
            {bgElement && (
              <div className="absolute inset-0 z-0 overflow-hidden pointer-events-none">
                {bgElement}
              </div>
            )}

            {}
            <div className="relative z-10 w-full flex flex-col items-center">
              {containerVariant !== 'none' ? (
                <BaseContainer variant={containerVariant} className={containerClassName}>
                  {children}
                </BaseContainer>
              ) : (
                children
              )}
            </div>
          </div>
          {extraContent}
        </BaseContainer>
      </section>
    );
  }

  return (
    <section id={id} className={cn("w-full relative z-0 py-12 md:py-20 lg:py-24", className)} {...props}>
      <div className="relative z-10 w-full flex flex-col items-center">
        <BaseContainer variant={containerVariant} className={containerClassName}>
          {children}
        </BaseContainer>
      </div>
    </section>
  );
}