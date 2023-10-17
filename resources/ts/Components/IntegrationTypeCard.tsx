import React from "react";
import { Card } from "./Card";
import { IntegrationType } from "./IntegrationTypes";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck } from "@fortawesome/free-solid-svg-icons";
import { useTranslation } from "react-i18next";
import { classNames } from "../utils/classNames";
import { router } from "@inertiajs/core";
import { useTranslateRoute } from "../hooks/useTranslateRoute";
import { ButtonSecondary } from "./ButtonSecondary";

type Props = IntegrationType;

export const IntegrationTypeCard = ({
  title,
  description,
  features,
  type,
}: Props) => {
  const { t } = useTranslation();
  const translateRoute = useTranslateRoute();
  const afterStyles =
    "md:after:hidden md:after:fixed md:after:bottom-[-2rem] md:after:right-[0rem] md:after:w-0 md:after:h-0 md:after:border-r-[3rem] md:after:border-r-white md:after:border-b-[2rem] md:after:border-b-transparent md:hover:after:block";
  const url = new URL(
    translateRoute("/integrations/new"),
    document.location.origin
  );
  const changeTypeInUrl = (tab: string) => {
    url.searchParams.set("type", tab);
    router.get(url.toString());
  };
  return (
    <Card
      key={title}
      title={title}
      description={description}
      className={classNames(
        "md:max-w-sm md:hover:translate-y-[-2rem] md:overflow-visible gap-7 md:transition md:duration-500",
        afterStyles
      )}
    >
      <div className="flex flex-col flex-1 justify-between min-h-[10rem] gap-7">
        <ul className="flex flex-col gap-3">
          {features.map((feature) => (
            <li key={feature}>
              <FontAwesomeIcon icon={faCheck} className="text-green-500" />{" "}
              {feature}
            </li>
          ))}
        </ul>
        <ButtonSecondary
          className="self-center"
          onClick={() => changeTypeInUrl(type)}
        >
          {t("home.integration_types.action", { type: title })}
        </ButtonSecondary>
      </div>
    </Card>
  );
};
