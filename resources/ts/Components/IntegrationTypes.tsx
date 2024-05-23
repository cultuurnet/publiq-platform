import React, { useMemo } from "react";
import { IntegrationTypeCard } from "./IntegrationTypeCard";
import { useTranslation } from "react-i18next";
import type { TFunction } from "i18next";
import { IntegrationType } from "../types/IntegrationType";
import { IconEntryApi } from "./icons/IconEntryApi";
import { IconSearchApi } from "./icons/IconSearchApi";
import { IconWidgets } from "./icons/IconWidgets";
import { classNames } from "../utils/classNames";
import { uitpasEnabled } from "../constants/uitpas";
import { IconUiTPAS } from "./icons/IconUiTPAS";

export const integrationIconClasses =
  "h-full w-auto aspect-square max-h-[10rem] object-contain";

export const integrationTypesIcons = {
  [IntegrationType.EntryApi]: IconEntryApi,
  [IntegrationType.SearchApi]: IconSearchApi,
  [IntegrationType.Widgets]: IconWidgets,
  [IntegrationType.UiTPAS]: IconUiTPAS,
};

export const getIntegrationTypesInfo = (t: TFunction) => [
  {
    Icon: IconUiTPAS,
    image: <IconUiTPAS className={classNames(integrationIconClasses)} />,
    title: t("home.integration_types.uitpas_api.title"),
    description: t("home.integration_types.uitpas_api.description"),
    features: [
      t("home.integration_types.uitpas_api.features.0"),
      t("home.integration_types.uitpas_api.features.1"),
      t("home.integration_types.uitpas_api.features.2"),
    ],
    type: IntegrationType.UiTPAS,
  },
  {
    Icon: IconEntryApi,
    image: (
      <IconEntryApi className={classNames(integrationIconClasses, "mr-4")} />
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
      <IconSearchApi className={classNames(integrationIconClasses, "ml-2")} />
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
      <IconWidgets className={classNames(integrationIconClasses, "mr-6")} />
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

export const useIntegrationTypesInfo = () => {
  const { t } = useTranslation();

  return useMemo(() => {
    const integrationTypesInfo = getIntegrationTypesInfo(t);

    // See https://jira.publiq.be/browse/PPF-481
    return uitpasEnabled
      ? integrationTypesInfo
      : integrationTypesInfo.filter(
          (integrationTypesInfo) =>
            integrationTypesInfo.type !== IntegrationType.UiTPAS
        );
  }, [t]);
};

export type IntegrationTypesInfo = ReturnType<
  typeof getIntegrationTypesInfo
>[number];

export const IntegrationTypes = () => {
  const filteredIntegrationTypes = useIntegrationTypesInfo();

  return (
    <div>
      <ul
        className={classNames(
          !uitpasEnabled && "w-full flex gap-5 max-md:flex-col",
          uitpasEnabled &&
            "grid grid-cols-4 gap-4 max-lg:grid-cols-2 max-md:grid-cols-1"
        )}
      >
        {filteredIntegrationTypes.map((integrationTypeInfo) => (
          <IntegrationTypeCard
            key={integrationTypeInfo.title}
            {...integrationTypeInfo}
          />
        ))}
      </ul>
    </div>
  );
};
