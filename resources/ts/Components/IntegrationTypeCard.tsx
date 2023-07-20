import React from "react";
import { Card } from "./Card";
import { IntegrationType } from "./IntegrationTypes";
import { Link } from "./Link";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck } from "@fortawesome/free-solid-svg-icons";
import { useTranslation } from "react-i18next";

type Props = IntegrationType;

export const IntegrationTypeCard = ({
  title,
  description,
  features,
  actionUrl,
}: Props) => {
  const { t } = useTranslation();
  return (
    <Card
      key={title}
      title={title}
      description={description}
      className="md:max-w-sm"
    >
      <div className="flex flex-col flex-1 justify-between min-h-[10rem]">
        <ul className="flex flex-col gap-3">
          {features.map((feature) => (
            <li key={feature}>
              <FontAwesomeIcon icon={faCheck} className="text-green-500" />{" "}
              {feature}
            </li>
          ))}
        </ul>
        <Link className="self-center" href={actionUrl}>
          {t("home.integration_types.more_info")}
        </Link>
      </div>
    </Card>
  );
};
