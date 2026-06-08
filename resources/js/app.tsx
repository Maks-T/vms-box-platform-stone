import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'VMS';

createInertiaApp({
  title: (title: string) => `${title} - ${appName}`,
  resolve: (name: string) => {
    const pages = import.meta.glob('./Pages/**/*.{js,jsx,ts,tsx}');
    const path = Object.keys(pages).find((p) => p.startsWith(`./Pages/${name}.`));

    if (!path) {
      throw new Error(`Страница не найдена: ./Pages/${name}`);
    }

    return pages[path]() as Promise<any>;
  },
  setup({ el, App, props }) {
    const root = createRoot(el);
    root.render(<App {...props} />);
  },
  progress: {
    color: '#0284c7',
  },
});