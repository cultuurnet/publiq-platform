import React, { useEffect, useState } from "react";
import Navigation from "./Navigation";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faBars } from "@fortawesome/free-solid-svg-icons";
import { Dialog } from "./Dialog";
import { router } from "@inertiajs/react";

export default function HeaderMobile() {
  const { t } = useTranslation();
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const removeListener = router.on("navigate", () => setIsVisible(false));

    return () => removeListener();
  }, []);

  return (
    <header className="w-full flex items-center justify-around bg-white shadow-lg  z-40 md:hidden">
      <Heading className="py-3 border-transparent border-b-4" level={3}>
        {t("title")}
      </Heading>
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
