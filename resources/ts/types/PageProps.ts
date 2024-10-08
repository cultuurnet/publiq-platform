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
  keycloak: {
    enabled: boolean;
    testClientEnabled: boolean;
  };
};

export type PageProps = {
  auth: {
    authenticated: boolean;
  };
  widgetConfig: WidgetConfigVariables;
  config: Config;
};
