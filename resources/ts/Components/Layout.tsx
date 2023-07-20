import React, { ReactNode } from "react";
import Header from "./Header";
import HeaderMobile from "./HeaderMobile";
import Footer from "./Footer";
import { SectionCollapsedProvider } from "../context/SectionCollapsedContext";
import { usePage } from "@inertiajs/react";

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
      <Header />
      <HeaderMobile />

      <Main>{children}</Main>
      <Footer />
    </div>
  );
}
