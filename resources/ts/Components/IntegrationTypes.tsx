import React, { useMemo } from "react";
import { IntegrationTypeCard } from "./IntegrationTypeCard";
import { useTranslation } from "react-i18next";
import { TFunction } from "i18next";
import { IntegrationType } from "../types/IntegrationType";

const getIntegrationTypes = (t: TFunction) => [
  {
    title: t("home.integration_types.entry_api.title"),
    description: t("home.integration_types.entry_api.description"),
    features: [
      t("home.integration_types.entry_api.features.0"),
      t("home.integration_types.entry_api.features.1"),
      t("home.integration_types.entry_api.features.2"),
    ],
    type: IntegrationType.EntryApi,
  },
  {
    title: t("home.integration_types.search_api.title"),
    description: t("home.integration_types.search_api.description"),
    features: [
      t("home.integration_types.search_api.features.0"),
      t("home.integration_types.search_api.features.1"),
      t("home.integration_types.search_api.features.2"),
    ],
    type: IntegrationType.SearchApi,
  },
  {
    title: t("home.integration_types.widgets.title"),
    description: t("home.integration_types.widgets.description"),
    features: [
      t("home.integration_types.widgets.features.0"),
      t("home.integration_types.widgets.features.1"),
      t("home.integration_types.widgets.features.2"),
    ],
    type: IntegrationType.Widgets,
  },
];

export type IntegrationType = ReturnType<typeof getIntegrationTypes>[number];

export const IntegrationTypes = () => {
  const { t } = useTranslation();

  const translatedIntegrationTypes = useMemo(() => getIntegrationTypes(t), [t]);

  return (
    <div>
      <ul className="w-full flex gap-5 mt-[2rem] max-md:flex-col">
        {translatedIntegrationTypes.map((integration) => (
          <IntegrationTypeCard key={integration.title} {...integration} />
        ))}
      </ul>
    </div>
  );
};
