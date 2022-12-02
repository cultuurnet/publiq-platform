import "./bootstrap";
import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/inertia-react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { initializeI18n } from "./i18n/initializeI18n";

createInertiaApp({
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.tsx`,
      // @ts-expect-error
      import.meta.glob("./Pages/**/*.tsx")
    ),
  setup({ el, App, props }) {
    initializeI18n().then(() => {
      const root = createRoot(el);
      root.render(<App {...props} />);
    });
  },
});
