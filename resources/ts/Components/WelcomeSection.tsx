import { ComponentProps, useMemo } from "react";
import React from "react";
import { classNames } from "../utils/classNames";
import { Heading } from "./Heading";
import { useTranslation } from "react-i18next";
import { TFunction } from "i18next";
import { IconEntryApi } from "./icons/IconEntryApi";
import { IconSearchApi } from "./icons/IconSearchApi";
import { IconWidgets } from "./icons/IconWidgets";
import { IconUiTPAS } from "./icons/IconUiTPAS";
import { WelcomeCard } from "./WelcomeCard";

const imageStyle = "w-28 h-28 object-contain";

const getWelcomeIntegrationTypes = (t: TFunction) => [
  {
    title: t("welcome_section.card.entry_api.title"),
    description: t("welcome_section.card.entry_api.description"),
    imgUrl: <IconEntryApi className={classNames(imageStyle, "mr-6")} />,
    actionTitle: t("welcome_section.card.entry_api.cta"),
    actionUrl: t("welcome_section.card.entry_api.action_url"),
    documentationUrl: t("welcome_section.card.entry_api.documentation_url"),
  },
  {
    title: t("welcome_section.card.search_api.title"),
    description: t("welcome_section.card.search_api.description"),
    imgUrl: <IconSearchApi className={imageStyle} />,
    actionTitle: t("welcome_section.card.search_api.cta"),
    actionUrl: t("welcome_section.card.search_api.action_url"),
    documentationUrl: t("welcome_section.card.search_api.documentation_url"),
  },
  {
    title: t("welcome_section.card.widgets.title"),
    description: t("welcome_section.card.widgets.description"),
    imgUrl: <IconWidgets className={classNames(imageStyle, "mr-10")} />,
    actionTitle: t("welcome_section.card.widgets.cta"),
    actionUrl: t("welcome_section.card.widgets.action_url"),
    documentationUrl: t("welcome_section.card.widgets.documentation_url"),
  },
  {
    title: t("welcome_section.card.uitpas.title"),
    description: t("welcome_section.card.uitpas.description"),
    imgUrl: <IconUiTPAS className={imageStyle} />,
    actionTitle: t("welcome_section.card.uitpas.cta"),
    actionUrl: t("welcome_section.card.uitpas.action_url"),
    documentationUrl: t("welcome_section.card.uitpas.documentation_url"),
  },
];

export type WelcomeIntegrationType = ReturnType<
  typeof getWelcomeIntegrationTypes
>[number];

type Props = ComponentProps<"section">;

export const WelcomeSection = ({ className, ...props }: Props) => {
  const { t } = useTranslation();
  const translatedWelcomeTypes = useMemo(
    () => getWelcomeIntegrationTypes(t),
    [t]
  );
  return (
    <section
      className={classNames(
        "flex flex-col items-start w-full gap-7 md:min-w-[40rem] max-w-screen-xl min-h-screen pb-8 mb-16 font-light tracking-wide",
        className
      )}
      {...props}
    >
      <Heading level={5} className="font-semibold">
        {t("welcome_section.title")}
      </Heading>
      <div className="flex flex-col gap-4">
        <p>{t("welcome_section.description.part1")}</p>
        <p>{t("welcome_section.description.part2")}</p>
      </div>
      <div>
        <ul className="flex justify-center gap-9 flex-wrap">
          {translatedWelcomeTypes.map((type) => (
            <WelcomeCard key={type.title} {...type} {...props} />
          ))}
        </ul>
      </div>
    </section>
  );
};
