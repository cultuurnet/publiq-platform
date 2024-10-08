import "./bootstrap";
import type { ReactNode } from "react";
import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { initializeI18n } from "./i18n/initializeI18n";
import * as Sentry from "@sentry/browser";
import type { PageProps } from "./types/PageProps";
import Layout from "./layouts/Layout";

type Page = {
  default: {
    layout?: unknown;
  };
};

createInertiaApp<PageProps>({
  resolve: (name) => {
    const pages = import.meta.glob("./Pages/**/*.tsx", {
      eager: true,
    }) as Record<string, Page>;

    const page = pages[`./Pages/${name}.tsx`];
    page.default.layout =
      page.default.layout || ((page: ReactNode) => <Layout>{page}</Layout>);
    return page;
  },
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
