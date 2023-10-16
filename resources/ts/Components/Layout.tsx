import React, { ReactNode } from "react";
import Header from "./Header";
import HeaderMobile from "./HeaderMobile";
import Footer from "./Footer";
import { usePage, Head } from "@inertiajs/react";
import { UitIdWidget, WidgetConfigVariables } from "./UitIdWidget";

const Main = ({ children }: { children: ReactNode }) => {
  const page = usePage();
  const classes = "flex flex-col items-center w-full";

  if (page.component === "Integrations/Detail") {
    return <main className={classes}>{children}</main>;
  }

  return <main className={classes}>{children}</main>;
};

export default function Layout({ children }: { children: ReactNode }) {
  const { widgetConfig } = usePage<{
    widgetConfig: WidgetConfigVariables;
  }>().props;

  return (
    <div className="flex flex-col flex-1 items-center text-publiq-gray-dark bg-publiq-gray-light">
      <Head>
        <script
          type="module"
          src="https://assets.uit.be/uitid-widget/index.js"
          async
        ></script>
      </Head>

      <UitIdWidget {...widgetConfig} />

      <Header />
      <HeaderMobile />
      <Main>{children}</Main>
      <Footer />
    </div>
  );
}
