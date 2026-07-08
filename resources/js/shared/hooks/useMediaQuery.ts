import { useState, useEffect } from "react";

export function useMediaQuery(query: string): boolean {
  const [value, setValue] = useState(false);

  useEffect(() => {
    const handler = (e: MediaQueryListEvent) => setValue(e.matches);
    const mediaQuery = window.matchMedia(query);
    setValue(mediaQuery.matches);
    mediaQuery.addEventListener("change", handler);
    return () => mediaQuery.removeEventListener("change", handler);
  }, [query]);

  return value;
}
