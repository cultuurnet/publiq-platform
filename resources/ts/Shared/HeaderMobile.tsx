import React, { useState } from "react";
import Navigation from "./Navigation";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faBars, faXmark } from "@fortawesome/free-solid-svg-icons";
import { Dialog } from "./Dialog";

export default function HeaderMobile() {
  const { t } = useTranslation();
  const [isVisible, setIsVisible] = useState(false);

  return (
    <header className="w-full flex items-center justify-around bg-white shadow-lg  z-40 md:hidden">
      <Heading className="py-3 border-transparent border-b-4" level={3}>
        {t("title")}
      </Heading>
      <Dialog isVisible={isVisible} onClose={() => setIsVisible(false)}>
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
