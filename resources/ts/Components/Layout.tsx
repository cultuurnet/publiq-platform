import React, { ReactNode } from "react";
import Header from "./Header";
import HeaderMobile from "./HeaderMobile";
import Footer from "./Footer";
import { SectionCollapsedProvider } from "../context/SectionCollapsedContext";
import { usePage, Head } from "@inertiajs/react";
import widgetConfig from "../../widget/widget-config.json";

const Main = ({ children }: { children: ReactNode }) => {
  const page = usePage();
  const classes = "flex flex-col items-center w-full";

  if (page.component === "Integrations/Detail") {
    return (
      <SectionCollapsedProvider>
        <main className={classes}>{children}</main>
      </SectionCollapsedProvider>
    );
  }

  return <main className={classes}>{children}</main>;
};

export default function Layout({ children }: { children: ReactNode }) {
  return (
    <div className="flex flex-col flex-1 items-center text-publiq-gray-dark bg-publiq-gray-light">
      <Head>
        <script
          type="module"
          src="https://assets.uit.be/uitid-widget/index.js"
          async
        ></script>
      </Head>

      <script id="uitid-widget-config" type="application/json">
        {JSON.stringify(widgetConfig)}
      </script>
      <div id="uitid-widget"></div>
      <Header />
      <HeaderMobile />
      <Main>{children}</Main>
      <Footer />
    </div>
  );
}
