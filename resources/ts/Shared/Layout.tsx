import React, { ReactNode } from 'react';
import Header from './Header';
import Footer from './Footer';

export default function Layout({ children }: { children: ReactNode }) {
  return (
    <div className="px-3 lg:px-6">
      <Header />
      <section className="pb-8">{children}</section>
      <Footer />
    </div>
  );
}
