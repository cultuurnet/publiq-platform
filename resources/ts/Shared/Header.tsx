import React from "react";
import Navigation from "./Navigation";

export default function Header() {
  return (
    <header className="w-full max-md:hidden bg-white shadow-lg z-40">
      <Navigation />
    </header>
  );
}
