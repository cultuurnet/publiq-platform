import React, { ReactNode } from "react";
import Header from "./Header";
import Footer from "./Footer";

export default function Layout({ children }: { children: ReactNode }) {
  return (
    <div className="flex flex-col flex-1 items-center text-publiq-gray-dark bg-publiq-gray-light">
      <Header />
      <main className="flex flex-col items-center pb-8 w-full">{children}</main>
      <Footer />
    </div>
  );
}
