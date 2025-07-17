export type WidgetConfigVariables = {
  url: string;
  profileUrl: string;
  registerUrl: string;
  oAuthDomain: string;
};

type Config = {
  env: string;
  sentry: {
    enabled: boolean;
    dsn: string;
  };
  coupons: {
    enabled: boolean;
  };
};

export type PageProps = {
  auth: {
    authenticated: boolean;
  };
  widgetConfig: WidgetConfigVariables;
  config: Config;
};
