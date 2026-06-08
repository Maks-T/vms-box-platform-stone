import * as React from "react";
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/shared/lib/utils";

const glassVariants = cva("transition-all duration-300 border", {
  variants: {
    variant: {
      default: "bg-white/[0.02] backdrop-blur-md border-white/5 shadow-card",
      deep: "bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-150 border-white/10",
      glow: "bg-white/[0.01] backdrop-blur-xl border-white/[0.08] shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]",
      brand: "bg-gradient-to-br from-primary/[0.10] to-transparent bg-[0_20px_50px_rgba(0,94,202,0.1)] backdrop-blur-xl border-primary/10 shadow-[0_20px_50px_rgba(0,94,202,0.1)]",
      light: "bg-white/70 backdrop-blur-xl border-slate-200/60 shadow-sm",
    },
    padding: {
      none: "p-0",
      sm: "p-4",
      md: "p-6 md:p-8",
      lg: "p-8 md:p-12",
    },
    interactive: {
      true: "hover:bg-white/[0.05] hover:border-white/20 cursor-pointer",
      false: ""
    }
  },
  defaultVariants: { variant: "default", padding: "md", interactive: false }
});

export interface GlassPanelProps extends React.HTMLAttributes<HTMLDivElement>, VariantProps<typeof glassVariants> {
  as?: React.ElementType;
}

const GlassPanel = React.forwardRef<HTMLDivElement, GlassPanelProps>(
  ({ children, variant, padding, interactive, className, as: Component = 'div', ...props }, ref) => {
    return (
      <Component ref={ref} className={cn(glassVariants({ variant, padding, interactive, className }), "rounded-2xl")} {...props}>
        {children}
      </Component>
    );
  }
);
GlassPanel.displayName = "GlassPanel";
export default GlassPanel;