import { useState, useEffect } from 'react';

export function useDevMode(): boolean {
  const [isDev, setIsDev] = useState<boolean>(() => {
    if (typeof window === 'undefined') return false;

    const urlParams = new URLSearchParams(window.location.search);
    const modeParam = urlParams.get('mode');

    if (modeParam === 'dev') {
      localStorage.setItem('app_mode', 'dev');
      return true;
    }
    if (modeParam === 'prod') {
      localStorage.setItem('app_mode', 'prod');
      return false;
    }

    return localStorage.getItem('app_mode') === 'dev';
  });

  useEffect(() => {
    const handleStorageChange = () => {
      setIsDev(localStorage.getItem('app_mode') === 'dev');
    };

    window.addEventListener('storage', handleStorageChange);
    return () => window.removeEventListener('storage', handleStorageChange);
  }, []);

  return isDev;
}