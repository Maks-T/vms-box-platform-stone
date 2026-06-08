import React from 'react';
import {cn} from '@/shared/lib/utils';
import {LucideIcon} from 'lucide-react';

interface SocialLinkProps {
  src?: string;
  icon?: LucideIcon | React.FC<any>;
  href?: string;
  alt?: string;
  size?: 'sm' | 'lg';
  className?: string;
}

export default function SocialLink({
                                     src,
                                     icon: Icon,
                                     href,
                                     alt = "social icon",
                                     size = "sm",
                                     className
                                   }: SocialLinkProps) {
  const sizeStyles = {sm: "w-11 h-11", lg: "w-[60px] h-[60px]"};
  const iconStyles = {sm: "w-5 h-5", lg: "w-6 h-6"};

  const content = (
    <>
      <div
        className="absolute inset-0 rounded-full opacity-0 group-hover:opacity-10 bg-white blur-md transition-opacity duration-500"/>
      {src ? (
        <img src={src} alt={alt}
             className={cn("relative z-10 object-contain transition-all duration-300 brightness-0 invert opacity-100 group-hover:drop-shadow-[0_0_3px_rgba(255,255,255,0.4)]", iconStyles[size])}/>
      ) : Icon ? (
        <Icon
          className={cn("relative z-10 transition-all duration-300 text-white opacity-100 group-hover:drop-shadow-[0_0_3px_rgba(255,255,255,0.4)]", iconStyles[size])}
          strokeWidth={2}/>
      ) : null}
    </>
  );

  const classes = cn(
    "group relative flex items-center justify-center rounded-full transition-all duration-300 ease-out bg-white/[0.03] backdrop-blur-md border border-white/10 hover:bg-white/[0.12] hover:border-white/40 active:scale-95 cursor-pointer overflow-hidden",
    sizeStyles[size], className
  );

  if (href) return <a href={href} target="_blank" rel="noopener noreferrer" className={classes}>{content}</a>;
  return <div className={classes}>{content}</div>;
}