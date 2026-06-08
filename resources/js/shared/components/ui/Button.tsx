import * as React from "react";
import { Slot } from "@radix-ui/react-slot";
import { cva, type VariantProps } from "class-variance-authority";
import { ArrowRight } from "lucide-react";
import { Link } from '@inertiajs/react';
import { cn } from "@/shared/lib/utils";

const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2.5 whitespace-nowrap font-medium transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 active:scale-[0.98] cursor-pointer group",
  {
    variants: {
      variant: {
        default: "bg-primary text-primary-foreground hover:opacity-90 shadow-sm",
        glass: "bg-white/[0.02] backdrop-blur-sm border border-white/5 shadow-sm text-white hover:bg-white/[0.06]",
        outline: "border border-border bg-transparent hover:bg-muted text-foreground",
        ghost: "hover:bg-muted text-foreground",
        action: "bg-primary text-primary-foreground rounded-[10px] hover:bg-primary/90",
      },
      size: {
        default: "h-10 px-4 py-2 rounded-lg text-sm",
        sm: "h-8 px-3 rounded-md text-xs",
        lg: "h-[52px] px-8 rounded-lg text-[15px]",
        action: "h-auto py-[18px] px-[36px] rounded-[10px] text-[16px]",
        icon: "h-[44px] w-[44px] rounded-lg",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
);

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean;
  withArrow?: boolean;
  href?: string;
  target?: string;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, asChild = false, withArrow = false, href, children, ...props }, ref) => {


    if (href) {
      const isExternal = href.startsWith('http') || props.target === '_blank';
      const Comp = isExternal ? 'a' : Link;

      return (
        // @ts-ignore
        <Comp
          href={href}
          className={cn(buttonVariants({ variant, size, className }))}
          {...props}
        >
          {children}
          {withArrow && <ArrowRight className="w-5 h-5 ml-1 transition-transform group-hover:translate-x-1" />}
        </Comp>
      );
    }

    const Comp = asChild ? Slot : "button";

    return (
      <Comp
        className={cn(buttonVariants({ variant, size, className }))}
        ref={ref}
        {...props}
      >
        {asChild ? children : (
          <>
            {children}
            {withArrow && <ArrowRight className="w-5 h-5 ml-1 transition-transform group-hover:translate-x-1" />}
          </>
        )}
      </Comp>
    );
  }
);

Button.displayName = "Button";
export { Button, buttonVariants };