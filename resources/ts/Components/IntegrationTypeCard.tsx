import React from "react";
import { Card } from "./Card";
import { IntegrationType } from "./IntegrationTypes";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck } from "@fortawesome/free-solid-svg-icons";
import { useTranslation } from "react-i18next";
import { classNames } from "../utils/classNames";
import { ButtonLinkSecondary } from "./ButtonLinkSecondary";

type Props = IntegrationType;

export const IntegrationTypeCard = ({
  title,
  description,
  features,
  actionUrl,
}: Props) => {
  const { t } = useTranslation();
  const after =
    "md:after:hidden md:after:fixed md:after:bottom-[-2rem] md:after:right-[0rem] md:after:w-0 md:after:h-0 md:after:border-r-[3rem] md:after:border-r-white md:after:border-b-[2rem] md:after:border-b-transparent md:hover:after:block";
  return (
    <Card
      key={title}
      title={title}
      description={description}
      className={classNames("md:max-w-sm md:hover:translate-y-[-2rem] md:overflow-visible gap-7", after)}
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
        <ButtonLinkSecondary className="self-center" href={actionUrl}>
          {t("home.integration_types.action", {type:title})}
        </ButtonLinkSecondary>
      </div>
    </Card>
  );
};
