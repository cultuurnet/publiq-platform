import React from "react";
import { useTranslation } from "react-i18next";
import { Heading } from "./Heading";
import { LinkButton } from "./LinkButton";
import { SecondaryLinkButton } from "./SecondaryLinkButton";

export const Hero = () => {
  const { t } = useTranslation();

  return (
    <section className="w-full flex flex-col items-center">
      <div className="flex flex-col gap-4 items-center py-8 px-12">
        <Heading level={2}>{t("home.hero.title")}</Heading>
        <p className="text-center">{t("home.hero.intro")}</p>
        <LinkButton href="#">{t("home.hero.start_here")}</LinkButton>
        <SecondaryLinkButton href="#">
          {t("home.hero.start_here")}
        </SecondaryLinkButton>
      </div>
    </section>
  );
};
