import React, { ReactNode } from "react";
import Header from "./Header";
import Footer from "./Footer";

export default function Layout({ children }: { children: ReactNode }) {
  return (
    <div className="flex flex-col items-center px-3 lg:px-6">
      <Header />
      <section className="pb-8 max-w-2xl">{children}</section>
      <Footer />
    </div>
  );
}
