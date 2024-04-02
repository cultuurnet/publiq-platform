import React, { useMemo } from "react";
import { useTranslation } from "react-i18next";
import type { TFunction } from "i18next";
import { SupportCard } from "./SupportCard";
import type { SupportProps } from "../Pages/Support/Index";
import { IconDocumentation } from "./icons/IconDocumentation";
import { IconStyleguide } from "./icons/IconStyleguide";
import { IconStatuspage } from "./icons/IconStatuspage";
import { IconRoadmap } from "./icons/IconRoadmap";
import { IconReleaseNotes } from "./icons/IconReleaseNotes";
import { IconSlack } from "./icons/IconSlack";
import { IconCustomizedSupport } from "./icons/IconCustomizedSupport";

const imageStyle =
  "h-full w-auto aspect-square max-h-[10rem] min-w-[15rem] max-sm:w-full object-cover";

const getSupportTypes = (t: TFunction) => [
  {
    type: "documentation",
    title: t("support.documentation.title"),
    description: t("support.documentation.description"),
    imgUrl: <IconDocumentation className={imageStyle} />,
    actionTitle: t("support.documentation.action_title"),
    actionUrl: t("support.documentation.action_url"),
  },
  {
    type: "styleguide",
    title: t("support.styleguide.title"),
    description: t("support.styleguide.description"),
    imgUrl: <IconStyleguide className={imageStyle} />,
    actionTitle: t("support.styleguide.action_title"),
    actionUrl: t("support.styleguide.action_url"),
  },
  {
    type: "statuspage",
    title: t("support.statuspage.title"),
    description: t("support.statuspage.description"),
    imgUrl: <IconStatuspage className={imageStyle} />,
    actionTitle: t("support.statuspage.action_title"),
    actionUrl: t("support.statuspage.action_url"),
  },
  {
    type: "roadmap",
    title: t("support.roadmap.title"),
    description: t("support.roadmap.description"),
    imgUrl: <IconRoadmap className={imageStyle} />,
    actionTitle: t("support.roadmap.action_title"),
    actionUrl: t("support.roadmap.action_url"),
  },
  {
    type: "release notes",
    title: t("support.release_notes.title"),
    description: t("support.release_notes.description"),
    imgUrl: <IconReleaseNotes className={imageStyle} />,
    actionTitle: t("support.release_notes.action_title"),
    actionUrl: t("support.release_notes.action_url"),
  },
  {
    type: "slack",
    title: t("support.support_via_slack.title"),
    description: t("support.support_via_slack.description"),
    imgUrl: <IconSlack className={imageStyle} />,
    actionTitle: t("support.support_via_slack.action_title"),
    actionUrl: t("support.support_via_slack.action_url"),
  },
  {
    type: "customized support",
    title: t("support.customized_support.title"),
    description: t("support.customized_support.description"),
    imgUrl: <IconCustomizedSupport className={imageStyle} />,
    actionTitle: t("support.customized_support.action_title"),
    actionUrl: t("support.customized_support.action_url"),
  },
];

export type SupportType = ReturnType<typeof getSupportTypes>[number];

type Props = SupportProps;

export const SupportTypes = (props: Props) => {
  const { t } = useTranslation();

  const translatedSupportTypes = useMemo(() => getSupportTypes(t), [t]);

  return (
    <div>
      <ul className="flex justify-center gap-9 flex-wrap">
        {translatedSupportTypes.map((support) => (
          <SupportCard key={support.title} {...support} {...props} />
        ))}
      </ul>
    </div>
  );
};
