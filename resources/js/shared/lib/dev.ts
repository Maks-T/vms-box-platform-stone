export function checkDevMode(): boolean {
  if (typeof window === 'undefined') return false;

  const params = new URLSearchParams(window.location.search);
  const modeParam = params.get('mode');

  if (modeParam === 'dev') {
    localStorage.setItem('vms_dev_mode', 'true');
    return true;
  }

  if (modeParam === 'prod') {
    localStorage.removeItem('vms_dev_mode');
    return false;
  }

  return localStorage.getItem('vms_dev_mode') === 'true';
}

/**
 * Принудительно устанавливает или сбрасывает режим разработчика
 */
export function setDevMode(active: boolean): void {
  if (typeof window === 'undefined') return;

  if (active) {
    localStorage.setItem('vms_dev_mode', 'true');
  } else {
    localStorage.removeItem('vms_dev_mode');
  }

  const url = new URL(window.location.href);
  url.searchParams.delete('mode');
  window.history.replaceState({}, '', url.toString());

  window.location.reload();
}
