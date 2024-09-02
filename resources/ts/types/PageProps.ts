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
  uitpas: {
    enabled: boolean;
  };
  keycloak: {
    enabled: boolean;
    testClientEnabled: boolean;
  };
};

export type PageProps = {
  widgetConfig: WidgetConfigVariables;
  config: Config;
};
