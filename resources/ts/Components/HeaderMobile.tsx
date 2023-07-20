import React, { useEffect, useState } from "react";
import Navigation from "./Navigation";
import { useTranslation } from "react-i18next";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faBars } from "@fortawesome/free-solid-svg-icons";
import { Dialog } from "./Dialog";
import { router } from "@inertiajs/react";
import { PubliqLogoMobile } from "./logos/PubliqLogoMobile";
import { Link } from "@inertiajs/react";

export default function HeaderMobile() {
  const { t } = useTranslation();
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const removeListener = router.on("navigate", () => setIsVisible(false));

    return () => removeListener();
  }, []);

  return (
    <header className="w-full min-h-[4rem] flex items-center justify-between px-7 bg-white shadow-lg  z-40 md:hidden">
      <Link href="/">
        <PubliqLogoMobile />
      </Link>
      <Dialog
        isVisible={isVisible}
        isFullscreen
        onClose={() => setIsVisible(false)}
      >
        <Navigation orientation="vertical" />
      </Dialog>
      <FontAwesomeIcon
        icon={faBars}
        size="lg"
        onClick={() => setIsVisible((prev) => !prev)}
      />
    </header>
  );
}
