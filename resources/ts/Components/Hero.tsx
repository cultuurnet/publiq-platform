import React from "react";
import { useTranslation } from "react-i18next";
import { Heading } from "./Heading";
import { ButtonLink } from "./ButtonLink";
import { useTranslateRoute } from "../hooks/useTranslateRoute";

export const Hero = () => {
  const { t } = useTranslation();
  const translateRoute = useTranslateRoute();

  return (
    <section className="w-full flex flex-col items-center">
      <div className="flex flex-col gap-7 items-center py-8 px-12">
        <Heading level={1} className="text-center font-bold">
          {t("home.hero.title")}
        </Heading>
        <Heading level={2} className="text-center font-light max-w-[37rem]">
          {t("home.hero.intro")}
        </Heading>
        <ButtonLink href={translateRoute("/integrations/new")}>
          {t("home.hero.start_here")}
        </ButtonLink>
      </div>
    </section>
  );
};
