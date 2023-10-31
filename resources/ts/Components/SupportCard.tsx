import React from "react";
import { SupportType } from "./SupportTypes";
import { Heading } from "./Heading";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faArrowUpRightFromSquare } from "@fortawesome/free-solid-svg-icons";
import { ButtonLinkSecondary } from "./ButtonLinkSecondary";
import { ButtonSecondary } from "./ButtonSecondary";
import { router } from "@inertiajs/react";
import { InformationDialog } from "./InformationDialog";
import { useTranslation } from "react-i18next";
import { SupportProps } from "../Pages/Support/Index";
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
    <div className="w-full flex flex-col bg-white shadow hover:bg-publiq-blue-light hover:bg-opacity-5">
      <div className="flex flex-1 max-sm:flex-col ">
        <div className="flex flex-shrink-0">
          <img
            src={imgUrl}
            alt={title}
            className="h-full w-auto aspect-square max-sm:max-h-[12rem] max-sm:w-full object-cover"
          />
        </div>
        <div className="flex flex-col p-4 max-sm:gap-5">
          <div className="flex flex-col gap-3 md:min-h-[10rem]">
            <Heading level={3}>{title}</Heading>
            <p className="max-md:text-sm">{description}</p>
          </div>
          <div className="flex max-sm:self-center">
            {type === "slack" ? (
              <>
                <ButtonSecondary
                  onClick={() => {
                    handleSlackInvitation();
                  }}
                >
                  {actionTitle}
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
              </>
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
