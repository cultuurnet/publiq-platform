export type WidgetConfigVariables = {
  profileUrl: string;
  registerUrl: string;
  auth0Domain: string;
};
export type PageProps = {
  widgetConfig: WidgetConfigVariables;
  config: {
    [key: string]: any;
    VITE_SENTRY_ENABLED: boolean;
    VITE_UITPAS_INTEGRATION_TYPE_ENABLED: boolean;
  };
};
