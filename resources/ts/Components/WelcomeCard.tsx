import React from "react";
import { Heading } from "./Heading";
import { ButtonLinkSecondary } from "./ButtonLinkSecondary";
import { WelcomeIntegrationType } from "./WelcomeSection";
import { useTranslation } from "react-i18next";
import { Link } from "./Link";

type Props = WelcomeIntegrationType;

export const WelcomeCard = ({
  imgUrl,
  title,
  description,
  actionUrl,
  actionTitle,
  documentationUrl,
}: Props) => {
  const { t } = useTranslation();
  return (
    <div className="w-full flex flex-col bg-white drop-shadow-card">
      <div className="flex flex-1 max-sm:flex-col py-6">
        <div className="flex items-center justify-center min-w-[13rem] max-sm:py-4">
          {imgUrl}
        </div>
        <div className="flex flex-col max-sm:pb-4 max-sm:px-4 gap-5">
          <div className="flex flex-col gap-3 pr-10">
            <Heading level={3} className="font-semibold">
              {title}
            </Heading>
            <p className="max-md:text-sm">{description}</p>
          </div>
          <div className="flex max-sm:self-center max-sm:flex-col gap-7 items-center">
            <ButtonLinkSecondary
              className="min-w-[19rem] max-sm:min-w-[10rem] max-sm:px-3"
              href={actionUrl}
            >
              {actionTitle}
            </ButtonLinkSecondary>
            <Link
              className="underline text-publiq-blue-dark max-sm:self-center"
              href={documentationUrl}
            >
              {t("welcome_section.card.documentation")}
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};
