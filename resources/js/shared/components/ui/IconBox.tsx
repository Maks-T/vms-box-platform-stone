import * as React from "react";
import {Slot} from "@radix-ui/react-slot";
import {cva, type VariantProps} from "class-variance-authority";
import {cn} from "@/shared/lib/utils";
import {LucideIcon} from "lucide-react";

const iconBoxVariants = cva(
  "inline-flex items-center justify-center shrink-0 transition-all duration-300",
  {
    variants: {
      variant: {
        glass: "bg-white/[0.02] backdrop-blur-md border border-white/5 text-white hover:bg-white/[0.08]",
        primary: "bg-primary/10 text-primary hover:bg-primary/20",
        social: "bg-[#16191B]/80 backdrop-blur-md border border-white/10 text-slate-400 hover:text-white hover:bg-[#1A1D21]",
        light: "bg-white border border-slate-200 shadow-sm text-slate-900 hover:shadow-md",
      },
      shape: {
        circle: "rounded-full",
        square: "rounded-xl",
      },
      size: {
        sm: "w-8 h-8",
        default: "w-11 h-11",
        lg: "w-14 h-14",
        xl: "w-16 h-16",
      },
    },
    defaultVariants: {variant: "glass", shape: "square", size: "default"},
  }
);

interface IconBoxProps extends React.HTMLAttributes<HTMLDivElement>, VariantProps<typeof iconBoxVariants> {
  icon?: LucideIcon | React.FC<any>;
  src?: string;
  alt?: string;
  iconClassName?: string;
  asChild?: boolean;
}

const IconBox = React.forwardRef<HTMLDivElement, IconBoxProps>(({
                                                                  className,
                                                                  variant,
                                                                  shape,
                                                                  size,
                                                                  icon: Icon,
                                                                  src,
                                                                  alt = "",
                                                                  iconClassName,
                                                                  asChild = false,
                                                                  children,
                                                                  ...props
                                                                }, ref) => {
  const Comp = asChild ? Slot : "div";
  const isInteractive = asChild || props.onClick;
  const combinedClasses = cn(iconBoxVariants({
    variant,
    shape,
    size
  }), isInteractive && "cursor-pointer active:scale-[0.95]", className);

  if (asChild) return <Comp className={combinedClasses} ref={ref} {...props}>{children}</Comp>;

  const innerIconClass = cn("w-1/2 h-1/2 object-contain", iconClassName);

  return (
    <Comp className={combinedClasses} ref={ref} {...props}>
      {src ? <img src={src} alt={alt} className={innerIconClass}/> : Icon ?
        <Icon className={innerIconClass} strokeWidth={1.5}/> : children}
    </Comp>
  );
});

IconBox.displayName = "IconBox";
export {IconBox, iconBoxVariants};