import axios, {AxiosInstance, InternalAxiosRequestConfig, AxiosError, AxiosResponse} from 'axios';
import {toast} from 'sonner';

export interface CustomAxiosRequestConfig extends InternalAxiosRequestConfig {
  skipErrorHandler?: boolean;
}

const client: AxiosInstance = axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
  },
  // withCredentials: true,
});

// Интерцептор запросов (Добавляет заголовки динамически)
client.interceptors.request.use((config: CustomAxiosRequestConfig) => {
  // актуальный язык
  const locale = localStorage.getItem('app_locale') || 'ru';

  config.headers['Accept-Language'] = locale;
  config.headers['X-Sales-Channel'] = 'widget'; 

  return config;
});

// Интерцептор ответов (Глобальная обработка ошибок)
client.interceptors.response.use(
  (response: AxiosResponse) => {
    return response;
  },
  (error: AxiosError) => {
    const config = error.config as CustomAxiosRequestConfig;

    // Если при вызове передали { skipErrorHandler: true }, то игнорируем ошибку здесь
    if (config?.skipErrorHandler) {
      return Promise.reject(error);
    }

    // Глобальные уведомления об ошибках через Sonner
    if (error.response) {
      const status = error.response.status;
      const data = error.response.data as any;

      if (status === 404) {
        toast.error('Данные не найдены (404)');
      } else if (status === 422) {
        toast.error(data.message || 'Ошибка проверки данных');
      } else if (status >= 500) {
        toast.error('Внутренняя ошибка сервера. Попробуйте позже.');
      } else {
        toast.error(data.message || 'Произошла ошибка при запросе');
      }
    } else if (error.request) {
      toast.error('Нет ответа от сервера. Проверьте подключение к сети.');
    } else {
      toast.error('Произошла ошибка при отправке запроса');
    }

    return Promise.reject(error);
  }
);

export default client;
