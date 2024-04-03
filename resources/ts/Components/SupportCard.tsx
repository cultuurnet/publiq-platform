import React from "react";
import type { SupportType } from "./SupportTypes";
import { Heading } from "./Heading";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faArrowUpRightFromSquare } from "@fortawesome/free-solid-svg-icons";
import { ButtonLinkSecondary } from "./ButtonLinkSecondary";
import { ButtonSecondary } from "./ButtonSecondary";
import { router } from "@inertiajs/react";
import { InformationDialog } from "./InformationDialog";
import { useTranslation } from "react-i18next";
import type { SupportProps } from "../Pages/Support/Index";
import { useTranslateRoute } from "../hooks/useTranslateRoute";

type Props = SupportType & SupportProps;

export const SupportCard = ({
  type,
  title,
  description,
  imgUrl,
  actionTitle,
  actionUrl,
  email,
  slackStatus,
}: Props) => {
  const { t } = useTranslation();
  const translateRoute = useTranslateRoute();
  const handleSlackInvitation = () => router.post("/support/slack");
  const handleRedirect = () => router.get(translateRoute("/support"));

  return (
    <div className="w-full flex flex-col bg-white drop-shadow-card">
      <div className="flex flex-1 max-sm:flex-col md:gap-5">
        <div className="flex flex-shrink-1">{imgUrl}</div>
        <div className="flex flex-col max-sm:pb-4 max-sm:px-4 md:p-4 gap-5">
          <div className="flex flex-col gap-3 pr-10">
            <Heading level={3} className="font-semibold">
              {title}
            </Heading>
            <p className="max-md:text-sm">{description}</p>
          </div>
          <div className="flex max-sm:self-center">
            {type === "slack" ? (
              <div className="flex gap-3">
                <ButtonLinkSecondary href={actionUrl}>
                  {actionTitle}
                  <FontAwesomeIcon icon={faArrowUpRightFromSquare} />
                </ButtonLinkSecondary>
                <ButtonSecondary
                  onClick={() => {
                    handleSlackInvitation();
                  }}
                >
                  {t("support.support_via_slack.invitation")}
                </ButtonSecondary>

                <InformationDialog
                  isVisible={!!slackStatus}
                  title={title}
                  info={
                    slackStatus === "success"
                      ? t("dialog.invite_success", { email: email })
                      : t("dialog.invite_error")
                  }
                  onConfirm={() => handleRedirect()}
                  onClose={() => handleRedirect()}
                ></InformationDialog>
              </div>
            ) : (
              <ButtonLinkSecondary
                className="min-w-[15rem] max-sm:min-w-[10rem] max-sm:px-3"
                href={actionUrl}
              >
                {actionTitle}
                <FontAwesomeIcon icon={faArrowUpRightFromSquare} />
              </ButtonLinkSecondary>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};
