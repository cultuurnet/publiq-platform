import React from "react";
import { Card } from "./Card";
import type { IntegrationTypesInfo } from "./IntegrationTypes";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faCheckSquare,
  faChevronRight,
} from "@fortawesome/free-solid-svg-icons";
import { useTranslation } from "react-i18next";
import { classNames } from "../utils/classNames";
import { router } from "@inertiajs/core";
import { useTranslateRoute } from "../hooks/useTranslateRoute";

type Props = IntegrationTypesInfo;

export const IntegrationTypeCard = ({
  title,
  image,
  description,
  features,
  type,
}: Props) => {
  const { t } = useTranslation();
  const translateRoute = useTranslateRoute();
  const afterStyles =
    "md:after:hidden md:after:absolute md:after:drop-shadow-triangle md:after:bottom-[-2rem] md:after:right-[0rem] md:after:w-0 md:after:h-0 md:after:border-r-[3rem] md:after:border-r-white md:after:border-b-[2rem] md:after:border-b-transparent md:hover:after:block";
  const url = new URL(
    translateRoute("/integrations/new"),
    document.location.origin
  );
  const changeTypeInUrl = (type: string) => {
    url.searchParams.set("type", type);
    router.get(url.toString());
  };
  return (
    <Card
      key={title}
      title={title}
      img={image}
      description={description}
      className={classNames(
        "md:max-w-sm md:hover:translate-y-[-2rem] md:overflow-visible md:transition-transform md:duration-500 group",
        afterStyles
      )}
      role="button"
      onClick={() => changeTypeInUrl(type)}
      headingStyles="group-hover:text-publiq-blue-dark"
      textCenter
    >
      <div className="flex flex-col flex-1 justify-between min-h-[10rem] gap-7">
        <ul className="flex flex-col gap-3">
          {features.map((feature) => (
            <li key={feature} className="flex items-start gap-2">
              <FontAwesomeIcon
                icon={faCheckSquare}
                className="text-green-500 mt-1"
                size="lg"
              />
              {feature}
            </li>
          ))}
        </ul>
        <div className="self-center flex items-center gap-2 text-publiq-blue group-hover:text-publiq-blue-dark group-hover:underline">
          {t("home.integration_types.action", { type: title })}
          <FontAwesomeIcon icon={faChevronRight} size="xs" />
        </div>
      </div>
    </Card>
  );
};
