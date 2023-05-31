import React from "react";
import { useTranslation } from "react-i18next";
import { Heading } from "./Heading";

export const Hero = () => {
  const { t } = useTranslation();

  return (
    <section className="w-full flex flex-col items-center">
      <div className="flex flex-col items-center py-8 px-12">
        <button className="flex flex-col items-center py-8 px-12"></button>
        <Heading level={2}>{t("home.hero.title")}</Heading>
        <p className="text-center">{t("home.hero.intro")}</p>
        <button>{t("home.hero.start_here")}</button>
      </div>
    </section>
  );
};
