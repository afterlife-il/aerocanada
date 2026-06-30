import type { ButtonHTMLAttributes, ReactNode } from "react";
import Link from "next/link";
import { cn } from "@/lib/utils";

const variants = {
  primary: "border-accent bg-accent text-accent-foreground hover:bg-[oklch(0.44_0.18_25)]",
  secondary: "border-border bg-panel text-foreground hover:bg-panel-muted",
  quiet: "border-transparent bg-transparent text-muted hover:bg-panel-muted hover:text-foreground",
  danger: "border-[oklch(0.58_0.19_25)] bg-[oklch(0.58_0.19_25)] text-white hover:bg-[oklch(0.5_0.19_25)]"
};

export function Button({
  className,
  variant = "secondary",
  ...props
}: ButtonHTMLAttributes<HTMLButtonElement> & { variant?: keyof typeof variants }) {
  return (
    <button
      className={cn(
        "inline-flex h-9 items-center justify-center rounded-md border px-3 text-sm font-semibold transition active:translate-y-px disabled:cursor-not-allowed disabled:opacity-50",
        variants[variant],
        className
      )}
      {...props}
    />
  );
}

export function ButtonLink({
  className,
  variant = "secondary",
  children,
  href
}: {
  className?: string;
  variant?: keyof typeof variants;
  children: ReactNode;
  href: string;
}) {
  return (
    <Link
      href={href}
      className={cn(
        "inline-flex h-9 items-center justify-center rounded-md border px-3 text-sm font-semibold transition active:translate-y-px",
        variants[variant],
        className
      )}
    >
      {children}
    </Link>
  );
}
