import React, { PropsWithChildren } from 'react';
import Header from '@/widgets/Header';
import Footer from '@/widgets/Footer/Footer';
import { cn } from "@/shared/lib/utils";
import { Toaster } from "sonner";
import FavoritesDrawer from '@/widgets/FavoritesDrawer';

interface MainLayoutProps extends PropsWithChildren {
  headerOverlaps?: boolean;
}

export default function MainLayout({ children, headerOverlaps = false }: MainLayoutProps) {
  return (
    <div className="flex flex-col min-h-screen bg-white text-[#333333] font-sans antialiased selection:bg-[#25CED1]/20 selection:text-[#212B36]">

      <div className={cn(
        "w-full z-50 transition-colors duration-300",
        headerOverlaps ? "absolute top-0 left-0 bg-transparent" : "relative bg-[#212B36]"
      )}>
        <Header />
      </div>

      <main className="flex-1 w-full flex flex-col">
        {children}
      </main>

      <Footer />

      <FavoritesDrawer />
      <Toaster position="top-right" richColors={false}/>
    </div>
  );
}
