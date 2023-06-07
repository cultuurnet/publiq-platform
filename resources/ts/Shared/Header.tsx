import React from "react";
import NavigationElements from "./NavigationElements";

export default function Header() {
  return (
    <header className="w-full max-md:hidden bg-white shadow-lg z-40">
      <NavigationElements />
    </header>
  );
}
