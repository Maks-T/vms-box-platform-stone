import {BootstrapProductType} from "@/types/catalog";
import React, {useState} from "react";
import {cn} from "@shared/lib/utils";
import {ChevronDown, ChevronRight} from "lucide-react";

export default function ProductTypeRow({ type }: { type: BootstrapProductType }) {
  const hasMeta = type.meta && Object.keys(type.meta).length > 0;

  const [isOpen, setIsOpen] = useState(false);

  return (
    <div className="flex flex-col p-3 rounded-xl bg-muted/50 border border-border/50 transition-all">

      <div
        className={cn(
          "flex items-center gap-2 transition-colors",
          hasMeta ? "cursor-pointer select-none hover:text-primary" : ""
        )}
        onClick={() => hasMeta && setIsOpen(!isOpen)}
      >

        {hasMeta ? (
          isOpen ? (
            <ChevronDown className="w-4 h-4 text-primary shrink-0" />
          ) : (
            <ChevronRight className="w-4 h-4 text-muted-foreground shrink-0" />
          )
        ) : (
          <div className="w-4 h-4 shrink-0" />
        )}

        <span className="font-semibold text-foreground text-sm">{type.name}</span>
        <span className="text-[10px] text-muted-foreground font-mono ml-auto">{type.code}</span>
      </div>

      {hasMeta && isOpen && (
        <div className="flex flex-wrap gap-1.5 pl-6 mt-3 pt-3 border-t border-border/40 animate-in fade-in slide-in-from-top-2 duration-200">
          {Object.entries(type.meta!).map(([key, value]) => (
            <div key={key} className="flex items-center gap-1.5 bg-white border border-border px-2 py-0.5 rounded text-[11px] shadow-sm">
              <span className="text-muted-foreground font-medium">{key}:</span>
              <span className="font-bold text-foreground">{String(value)}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
