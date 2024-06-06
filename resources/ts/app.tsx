import "./bootstrap";
import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { initializeI18n } from "./i18n/initializeI18n";
import * as Sentry from "@sentry/browser";
import { PageProps } from "./types/PageProps";

createInertiaApp<PageProps>({
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.tsx`,
      import.meta.glob("./Pages/**/*.tsx")
    ),
  setup({ el, App, props }) {
    const config = props.initialPage.props.config;

    Sentry.init({
      dsn: config.VITE_SENTRY_DSN,
      environment: config.VITE_APP_ENV,
      enabled: config.VITE_SENTRY_ENABLED,
    });

    initializeI18n().then(() => {
      const root = createRoot(el);
      root.render(<App {...props} />);
    });
  },
});
