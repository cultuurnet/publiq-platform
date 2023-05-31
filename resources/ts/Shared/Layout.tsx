import React, { ReactNode } from "react";
import Header from "./Header";
import Footer from "./Footer";

export default function Layout({ children }: { children: ReactNode }) {
  return (
    <div className="flex flex-col items-center">
      <Header />
      <section className="pb-8 w-full">{children}</section>
      <Footer />
    </div>
  );
}