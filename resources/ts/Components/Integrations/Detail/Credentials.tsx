import React from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import type { Integration } from "../../../Pages/Integrations/Index";
import { StatusLight } from "../../StatusLight";
import { ButtonPrimary } from "../../ButtonPrimary";
import { Link } from "../../Link";

type Props = Integration;

export const Credentials = ({ id, status, hasCredentials }: Props) => {
  const { t } = useTranslation();

  return (
    <>
      <div>
        {hasCredentials.v2 && (
          <div className="w-full max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6 border-b pb-10 border-gray-300">
            <Heading className="font-semibold" level={3}>
              {t("details.credentials.uitid_v1")}
            </Heading>
            <div className="flex flex-col gap-6 min-w-[40rem]">
              <div>
                <Heading className="font-semibold flex" level={4}>
                  {t("details.credentials.test")}
                </Heading>
                <p>{id}</p>
              </div>
              <div>
                <Heading className="font-semibold" level={4}>
                  {t("details.credentials.live")}
                </Heading>
                <div className="flex gap-1 align-center">
                  <StatusLight status={status} />
                </div>
              </div>
            </div>
            {status === "pending_approval_payment" && (
              <div className="flex flex-col gap-4">
                <p className="ml-[5rem] max-w-[25rem]">
                  {t("details.credentials.status_alert")}
                  <Link
                    className="pl-1"
                    href={
                      "https://docs.publiq.be/docs/uitdatabank/entry-api/requirements-before-going-live"
                    }
                  >
                    {t("details.integration_info.link")}
                  </Link>
                </p>
                <ButtonPrimary className="ml-[5rem] self-start">
                  {t("details.credentials.action_status")}
                </ButtonPrimary>
              </div>
            )}
          </div>
        )}
      </div>
      <div className="w-full max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6">
        <Heading className="font-semibold" level={3}>
          {t("details.credentials.uitid_v2")}
        </Heading>
        {hasCredentials.v1 ? (
          <div className="flex flex-col gap-4">
            <p>{t("details.credentials.uitid_alert")}</p>
            <ButtonPrimary className="self-start">
              {t("details.credentials.action_uitid")}
            </ButtonPrimary>
          </div>
        ) : (
          <div>
            <div className="flex">
              <Heading className="font-semibold flex min-w-[5rem]" level={4}>
                {t("details.credentials.test")}
              </Heading>
              <p>{id}</p>
            </div>
            <div className="flex">
              <Heading className="font-semibold min-w-[5rem]" level={4}>
                {t("details.credentials.live")}
              </Heading>
              <div className="flex gap-1 align-center">
                <StatusLight status={status} />
                <Heading level={5}>
                  {t(`integrations.status.${status}`)}
                </Heading>
              </div>
            </div>
            {status === "draft" && (
              <div className="flex flex-col gap-4">
                <p className="ml-[5rem] max-w-[25rem]">
                  {t("details.credentials.status_alert")}
                </p>
                <ButtonPrimary className="ml-[5rem] self-start">
                  {t("details.credentials.action_status")}
                </ButtonPrimary>
              </div>
            )}
          </div>
        )}
      </div>
    </>
  );
};
