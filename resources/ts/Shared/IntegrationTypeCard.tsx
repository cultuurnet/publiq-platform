import React from "react";
import { Card } from "./Card";
import { IntegrationType } from "./IntegrationTypes";

type Props = IntegrationType;

export const IntegrationTypeCard = ({
  title,
  description,
  features,
  actionUrl,
}: Props) => {
  return (
    <Card key={title} title={title} description={description}>
      <div className="flex flex-col gap-6 items-center">
        <ul className="flex flex-col gap-3">
          {features.map((feature) => (
            <li key={feature}>✅ {feature}</li>
          ))}
        </ul>
        <a href={actionUrl}>Meer info</a>
      </div>
    </Card>
  );
};
