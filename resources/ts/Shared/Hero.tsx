import React from "react";
import { useTranslation } from "react-i18next";
import { Heading } from "./Heading";
import { ButtonLink } from "./ButtonLink";
import { ButtonLinkSecondary } from "./ButtonLinkSecondary";

export const Hero = () => {
  const { t } = useTranslation();

  return (
    <section className="w-full flex flex-col items-center">
      <div className="flex flex-col gap-4 items-center py-8 px-12">
        <Heading level={2}>{t("home.hero.title")}</Heading>
        <p className="text-center">{t("home.hero.intro")}</p>
        <ButtonLink href="#">{t("home.hero.start_here")}</ButtonLink>
        <ButtonLinkSecondary href="#">
          {t("home.hero.start_here")}
        </ButtonLinkSecondary>
      </div>
    </section>
  );
};
