import React, { useState } from "react";
import Navigation from "./Navigation";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faBars, faXmark } from "@fortawesome/free-solid-svg-icons";

export default function HeaderMobile() {
  const { t } = useTranslation();
  const [isVisible, setIsVisible] = useState(false);
  return (
    <header className="w-full flex items-center justify-around bg-white shadow-lg z-40 md:hidden">
      <Heading className="py-3 border-transparent border-b-4" level={3}>
        {t("title")}
      </Heading>
      <div>
        <Navigation
          visible={isVisible}
          orientation="vertical"
          className="fixed top-[1rem] right-[1rem] left-[1rem] bottom-[1rem] bg-publiq-gray-light shadow-lg"
          isVisible
          setIsVisible={setIsVisible}
        >
          <FontAwesomeIcon
            icon={faXmark}
            size="lg"
            onClick={() => setIsVisible((prev) => !prev)}
            className="text-publiq-blue-dark"
          />
        </Navigation>
      </div>
      <FontAwesomeIcon
        icon={faBars}
        onClick={() => setIsVisible((prev) => !prev)}
      />
    </header>
  );
}
