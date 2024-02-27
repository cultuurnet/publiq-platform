import React, { useMemo } from "react";
import { IntegrationTypeCard } from "./IntegrationTypeCard";
import { useTranslation } from "react-i18next";
import { TFunction } from "i18next";
import { IntegrationType } from "../types/IntegrationType";
import { IconEntryApi } from "./icons/IconEntryApi";
import { IconSearchApi } from "./icons/IconSearchApi";
import { IconWidgets } from "./icons/IconWidgets";
import { classNames } from "../utils/classNames";

export const integrationIconClasses =
  "h-full w-auto aspect-square max-h-[10rem] object-contain";

export const getIntegrationTypes = (t: TFunction) => [
  {
    Icon: IconEntryApi,
    image: (
      <IconEntryApi className={classNames(integrationIconClasses, "pr-4")} />
    ),
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
    Icon: IconSearchApi,
    image: (
      <IconSearchApi className={classNames(integrationIconClasses, "pl-2")} />
    ),
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
    Icon: IconWidgets,
    image: (
      <IconWidgets className={classNames(integrationIconClasses, "pr-6")} />
    ),
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

export const useIntegrationTypes = () => {
  const { t } = useTranslation();

  return useMemo(() => getIntegrationTypes(t), [t]);
};

export type IntegrationType = ReturnType<typeof getIntegrationTypes>[number];

export const IntegrationTypes = () => {
  const translatedIntegrationTypes = useIntegrationTypes();

  return (
    <div>
      <ul className="w-full flex gap-5 max-md:flex-col">
        {translatedIntegrationTypes.map((integration) => (
          <IntegrationTypeCard key={integration.title} {...integration} />
        ))}
      </ul>
    </div>
  );
};
