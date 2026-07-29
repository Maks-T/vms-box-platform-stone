import React from 'react';
import {cn} from '@/shared/lib/utils';

interface WaveBackgroundProps {
  className?: string;
}

export default function WaveBackground({className}: WaveBackgroundProps) {
  return (
    <div
      className={cn("absolute inset-0 overflow-hidden pointer-events-none z-0 rounded-[24px] lg:rounded-[32px]", className)}
      aria-hidden="true"
    >

      <style>{`
        @keyframes lava-blob-1 {
          0%, 100% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
          33% { transform: translate(80px, -30px) scale(1.15) rotate(120deg); }
          66% { transform: translate(-40px, 20px) scale(0.9) rotate(240deg); }
        }
        @keyframes lava-blob-2 {
          0%, 100% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
          33% { transform: translate(-60px, 40px) scale(1.2) rotate(-120deg); }
          66% { transform: translate(50px, -20px) scale(0.85) rotate(-240deg); }
        }
        @keyframes lava-blob-3 {
          0%, 100% { transform: translate(0px, 0px) scale(1); }
          33% { transform: translate(50px, 30px) scale(0.95); }
          66% { transform: translate(-50px, -30px) scale(1.1); }
        }
        .animate-lava-1 {
          animation: lava-blob-1 14s ease-in-out infinite;
          will-change: transform;
        }
        .animate-lava-2 {
          animation: lava-blob-2 18s ease-in-out infinite;
          will-change: transform;
        }
        .animate-lava-3 {
          animation: lava-blob-3 22s ease-in-out infinite;
          will-change: transform;
        }
      `}</style>

      <div className="absolute inset-0 filter blur-[50px] md:blur-[70px] opacity-70">

        <div
          className="animate-lava-1 absolute -top-12 left-10 w-72 h-72 rounded-full bg-gradient-to-tr from-[#005ECA] to-[#3D98FF] mix-blend-screen opacity-80"/>

        <div
          className="animate-lava-2 absolute -bottom-12 right-12 w-80 h-80 rounded-full bg-gradient-to-br from-[#0284c7] to-[#38bdf8] mix-blend-screen opacity-70"/>

        <div
          className="animate-lava-3 absolute top-1/2 left-1/3 -translate-y-1/2 w-96 h-56 rounded-full bg-gradient-to-r from-[#003F87] to-[#005ECA] mix-blend-screen opacity-60"/>
      </div>

      <div
        className="absolute inset-0 bg-gradient-to-t from-[#0B0F19]/80 via-transparent to-[#0B0F19]/50 pointer-events-none"/>
    </div>
  );
}