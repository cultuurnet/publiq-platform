import type { ReactNode } from "react";
import React from "react";
import Footer from "../Components/Footer";

const Main = ({ children }: { children: ReactNode }) => {
  const classes = "flex flex-col items-center w-full";

  return <main className={classes}>{children}</main>;
};

export default function HomePageLayout({ children }: { children: ReactNode }) {
  return (
    <div className="flex flex-col flex-1 items-center text-publiq-gray-900 bg-publiq-gray-75">
      <Main>{children}</Main>
      <Footer />
    </div>
  );
}
