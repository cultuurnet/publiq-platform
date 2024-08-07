import type { ReactNode } from "react";
import React from "react";
import Header from "../Components/Header";
import HeaderMobile from "../Components/HeaderMobile";
import Footer from "../Components/Footer";
import { Head } from "@inertiajs/react";
import { UitIdWidget } from "../Components/UitIdWidget";
import { usePageProps } from "../hooks/usePageProps";

const Main = ({ children }: { children: ReactNode }) => (
  <main className={"flex flex-col items-center w-full pt-6"}>{children}</main>
);

export default function Layout({ children }: { children: ReactNode }) {
  const { widgetConfig } = usePageProps();

  return (
    <div className="flex flex-col flex-1 items-center text-publiq-gray-900 bg-publiq-gray-75">
      <Head>
        <script
          type="module"
          src={`${widgetConfig.url}index.js`}
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
