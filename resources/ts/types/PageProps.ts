export type WidgetConfigVariables = {
  url: string;
  profileUrl: string;
  registerUrl: string;
  auth0Domain: string;
};

type Config = {
  env: string;
  sentry: {
    enabled: boolean;
    dsn: string;
  };
};

export type PageProps = {
  auth: {
    authenticated: boolean;
  };
  widgetConfig: WidgetConfigVariables;
  config: Config;
};
