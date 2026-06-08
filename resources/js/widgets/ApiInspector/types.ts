export interface ApiRequestInfo {
  label: string;
  method?: string;
  endpoint: string;
  headers?: Record<string, string>;
  data: any;
}
