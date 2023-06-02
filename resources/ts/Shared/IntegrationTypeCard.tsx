import React from "react";
import { Card } from "./Card";
import { IntegrationType } from "./IntegrationTypes";
import { Link } from "./Link";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck } from "@fortawesome/free-solid-svg-icons";

type Props = IntegrationType;

export const IntegrationTypeCard = ({
  title,
  description,
  features,
  actionUrl,
}: Props) => {
  return (
    <Card key={title} title={title} description={description}>
      <div className="flex flex-col gap-6">
        <ul className="flex flex-col gap-3">
          {features.map((feature) => (
            <li key={feature}>
              <FontAwesomeIcon icon={faCheck} className="text-green-500" />{" "}
              {feature}
            </li>
          ))}
        </ul>
        <Link className="self-center" href={actionUrl}>
          Meer info
        </Link>
      </div>
    </Card>
  );
};
