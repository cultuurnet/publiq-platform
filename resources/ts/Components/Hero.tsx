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
      <div className="flex flex-col gap-7 items-center py-0 px-6">
        <Heading
          level={1}
          className="text-center font-bold text-publiq-gray-dark"
        >
          {t("home.hero.title")}
        </Heading>
        <Heading
          level={2}
          className="text-center font-extralight text-publiq-gray-dark max-w-[37rem]"
        >
          {t("home.hero.intro")}
        </Heading>
        <ButtonLink
          href={translateRoute("/integrations")}
          className={"rounded-none text-xl mb-6"}
        >
          {t("home.hero.start_here")}
        </ButtonLink>
      </div>
    </section>
  );
};
