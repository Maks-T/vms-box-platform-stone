import React from 'react';
import {cn} from '@/shared/lib/utils';

interface WaveBackgroundProps {
  className?: string;
}

export default function WaveBackground({className}: WaveBackgroundProps) {
  return (
    <div className={cn("absolute inset-0 overflow-hidden pointer-events-none z-0", className)} aria-hidden="true">
      {}
      <style>{`
        @keyframes css-wave {
          0%   { transform: translateX(0) rotate(0deg); }
          50%  { transform: translateX(-25%) rotate(1deg); }
          100% { transform: translateX(-50%) rotate(0deg); }
        }
        .css-wave-layer {
          position: absolute;
          left: 0;
          width: 300%;
          background: linear-gradient(to top, rgba(14, 165, 233, 0.28) 0%, rgba(56, 189, 248, 0.12) 40%, transparent 75%);
          animation: css-wave linear infinite;
        }
      `}</style>

      {}
      <div
        className="absolute bottom-0 left-0 w-full h-[50%] bg-[radial-gradient(ellipse_at_50%_90%,rgba(14,165,233,0.25)_0%,rgba(56,189,248,0.08)_40%,transparent_70%)] z-10"/>

      {}
      <div className="css-wave-layer"
           style={{height: '320px', animationDuration: '32s', opacity: 0.35, filter: 'blur(6px)', bottom: '-40px'}}/>
      <div className="css-wave-layer"
           style={{height: '290px', animationDuration: '27s', opacity: 0.55, filter: 'blur(3px)', bottom: '-10px'}}/>
      <div className="css-wave-layer"
           style={{height: '260px', animationDuration: '19s', opacity: 0.45, filter: 'blur(5px)', bottom: '20px'}}/>
      <div className="css-wave-layer"
           style={{height: '230px', animationDuration: '29s', opacity: 0.6, filter: 'blur(2.5px)', bottom: '45px'}}/>
      <div className="css-wave-layer"
           style={{height: '200px', animationDuration: '23s', opacity: 0.4, filter: 'blur(4px)', bottom: '70px'}}/>
      <div className="css-wave-layer"
           style={{height: '170px', animationDuration: '34s', opacity: 0.3, filter: 'blur(6px)', bottom: '95px'}}/>
      <div className="css-wave-layer"
           style={{height: '140px', animationDuration: '21s', opacity: 0.25, filter: 'blur(3px)', bottom: '115px'}}/>
    </div>
  );
}