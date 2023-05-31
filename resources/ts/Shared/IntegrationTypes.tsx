import React from "react";
import { IntegrationTypeCard } from "./IntegrationTypeCard";

const integrationTypes = [
  {
    title: "Entry API",
    description:
      "Haal evenementinformatie op uit de UiTdatabank via onze nieuwste zoekengine.",
    features: [
      "JSON-standaard",
      "Award-winning API",
      "Activatie op live-omgeving op aanvraag",
    ],
    actionUrl: "#",
  },
  {
    title: "Search API",
    description:
      "Haal evenementinformatie op uit de UiTdatabank via onze nieuwste zoekengine.",
    features: [
      "JSON-standaard",
      "Award-winning API",
      "Activatie op live-omgeving op aanvraag",
    ],
    actionUrl: "#",
  },
  {
    title: "Widgets",
    description:
      "Haal evenementinformatie op uit de UiTdatabank via onze nieuwste zoekengine.",
    features: [
      "JSON-standaard",
      "Award-winning API",
      "Activatie op live-omgeving op aanvraag",
    ],
    actionUrl: "#",
  },
];

export type IntegrationType = (typeof integrationTypes)[number];

export const IntegrationTypes = () => {
  return (
    <div>
      <ul className="flex justify-center gap-4 flex-wrap">
        {integrationTypes.map((integration) => (
          <IntegrationTypeCard key={integration.title} {...integration} />
        ))}
      </ul>
    </div>
  );
};
