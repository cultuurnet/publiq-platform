export type WidgetConfigVariables = {
  url: string;
  profileUrl: string;
  registerUrl: string;
  auth0Domain: string;
};
export type PageProps = {
  widgetConfig: WidgetConfigVariables;
  config: { [key: string]: string } & {
    sentryEnabled: boolean;
    uitpasEnabled: boolean;
  };
};
