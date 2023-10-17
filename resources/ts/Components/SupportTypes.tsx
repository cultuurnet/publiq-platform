import React, { useMemo } from "react";
import { useTranslation } from "react-i18next";
import { TFunction } from "i18next";
import { SupportCard } from "./SupportCard";

const getSupportTypes = (t: TFunction) => [
  {
    title: t("support.documentation.title"),
    description: t("support.documentation.description"),
    imgUrl: t("support.documentation.img_url"),
    actionTitle: t("support.documentation.action_title"),
    actionUrl: t("support.documentation.action_url"),
  },
  {
    title: t("support.styleguide.title"),
    description: t("support.styleguide.description"),
    imgUrl: t("support.styleguide.img_url"),
    actionTitle: t("support.styleguide.action_title"),
    actionUrl: t("support.styleguide.action_url"),
  },
  {
    title: t("support.statuspage.title"),
    description: t("support.statuspage.description"),
    imgUrl: t("support.statuspage.img_url"),
    actionTitle: t("support.statuspage.action_title"),
    actionUrl: t("support.statuspage.action_url"),
  },
  {
    title: t("support.roadmap.title"),
    description: t("support.roadmap.description"),
    imgUrl: t("support.roadmap.img_url"),
    actionTitle: t("support.roadmap.action_title"),
    actionUrl: t("support.roadmap.action_url"),
  },
  {
  {
    title: t("support.support_via_slack.title"),
    description: t("support.support_via_slack.description"),
    imgUrl: t("support.support_via_slacksupport.img_url"),
    actionTitle: t("support.support_via_slack.action_title"),
    actionUrl: t("support.support_via_slack.action_url"),
  },
  {
    title: t("support.customized_support.title"),
    description: t("support.customized_support.description"),
    imgUrl: t("support.support_via_slacksupport.img_url"),
    actionTitle: t("support.customized_support.action_title"),
    actionUrl: t("support.customized_support.action_url"),
  },
];

export type SupportType = ReturnType<typeof getSupportTypes>[number];

export const SupportTypes = () => {
  const { t } = useTranslation();

  const translatedSupportTypes = useMemo(() => getSupportTypes(t), [t]);

  return (
    <div>
      <ul className="flex justify-center gap-4 flex-wrap">
        {translatedSupportTypes.map((support) => (
          <SupportCard key={support.title} {...support} />
        ))}
      </ul>
    </div>
  );
};
