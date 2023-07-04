import React, { ReactNode } from "react";
import Header from "./Header";
import HeaderMobile from "./HeaderMobile";
import Footer from "./Footer";

export default function Layout({ children }: { children: ReactNode }) {
  return (
    <div className="flex flex-col flex-1 items-center text-publiq-gray-dark bg-publiq-gray-light">
      <Header />
      <HeaderMobile />
      <main className="flex flex-col items-center w-full">{children}</main>
      <Footer />
    </div>
  );
}
