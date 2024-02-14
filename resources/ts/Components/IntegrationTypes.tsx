import React, { useMemo } from "react";
import { IntegrationTypeCard } from "./IntegrationTypeCard";
import { useTranslation } from "react-i18next";
import { TFunction } from "i18next";
import { IntegrationType } from "../types/IntegrationType";
import { IconEntryApi } from "./icons/IconEntryApi";
import { IconSearchApi } from "./icons/IconSearchApi";
import { IconWidgets } from "./icons/IconWidgets";
import { classNames } from "../utils/classNames";

export const getIntegrationTypes = (t: TFunction) => [
  {
    image: <IconEntryApi className={classNames(imageStyle, "pr-4")} />,
    title: t("home.integration_types.entry_api.title"),
    description: t("home.integration_types.entry_api.description"),
    features: [
      t("home.integration_types.entry_api.features.0"),
      t("home.integration_types.entry_api.features.1"),
      t("home.integration_types.entry_api.features.2"),
    ],
    type: IntegrationType.EntryApi,
    img: "",
  },
  {
    image: <IconSearchApi className={classNames(imageStyle, "pl-2")} />,
    title: t("home.integration_types.search_api.title"),
    description: t("home.integration_types.search_api.description"),
    features: [
      t("home.integration_types.search_api.features.0"),
      t("home.integration_types.search_api.features.1"),
      t("home.integration_types.search_api.features.2"),
    ],
    type: IntegrationType.SearchApi,
    img: "",
  },
  {
    image: <IconWidgets className={classNames(imageStyle, "pr-6")} />,
    title: t("home.integration_types.widgets.title"),
    description: t("home.integration_types.widgets.description"),
    features: [
      t("home.integration_types.widgets.features.0"),
      t("home.integration_types.widgets.features.1"),
      t("home.integration_types.widgets.features.2"),
    ],
    type: IntegrationType.Widgets,
    img: "",
  },
];

export const useIntegrationTypes = () => {
  const { t } = useTranslation();

  return useMemo(() => getIntegrationTypes(t), [t]);
};

export const imageStyle =
  "h-full w-auto aspect-square max-h-[10rem] object-contain";

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
