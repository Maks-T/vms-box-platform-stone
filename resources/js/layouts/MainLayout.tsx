import React, { PropsWithChildren, useState, useEffect } from 'react';
import Header from '@/widgets/Header';
import Footer from '@/widgets/Footer/Footer';
import { cn } from "@/shared/lib/utils";
import {Toaster} from "sonner";

interface MainLayoutProps extends PropsWithChildren {
  headerOverlaps?: boolean;
}

export default function MainLayout({ children, headerOverlaps = false }: MainLayoutProps) {
  return (
    <div className="flex flex-col min-h-screen bg-slate-50 font-sans">

      {}
      <div className={cn(
        "w-full z-50 transition-colors duration-300",
        headerOverlaps ? "absolute top-0 left-0 bg-transparent" : "relative bg-[#16191B]"
      )}>
        <Header />
      </div>

      <main className="flex-1 w-full flex flex-col">
        {children}
      </main>

      <Footer />

      <Toaster position="top-right" richColors={false}/>
    </div>
  );
}