import "./bootstrap";
import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { initializeI18n } from "./i18n/initializeI18n";
import * as Sentry from "@sentry/browser";
import type { PageProps } from "./types/PageProps";

createInertiaApp<PageProps>({
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.tsx`,
      import.meta.glob("./Pages/**/*.tsx")
    ),
  setup({ el, App, props }) {
    const config = props.initialPage.props.config;

    Sentry.init({
      dsn: config.sentry.dsn,
      environment: config.env,
      enabled: config.sentry.enabled,
    });

    initializeI18n().then(() => {
      const root = createRoot(el);
      root.render(<App {...props} />);
    });
  },
});
