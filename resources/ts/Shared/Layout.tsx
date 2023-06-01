import React, { ReactNode } from "react";
import Header from "./Header";
import Footer from "./Footer";

export default function Layout({ children }: { children: ReactNode }) {
  return (
    <div className="flex flex-col items-center text-textColor">
      <Header />
      <section className="pb-8 w-full">{children}</section>
      <Footer />
    </div>
  );
}
