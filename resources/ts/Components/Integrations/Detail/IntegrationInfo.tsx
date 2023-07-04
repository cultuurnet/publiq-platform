import React from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import type { Integration } from "../../../Pages/Integrations/Index";
import { StatusLight } from "../../StatusLight";
import { Button } from "../../Button";
import { Link } from "../../Link";

type Props = Integration;

export const IntegrationInfo = ({ id, status }: Props) => {
  const { t } = useTranslation();

  const v1 = true;
  const v2 = true;

  return (
    <div className="flex flex-col gap-4 shadow-md shadow-slate-200 px-10 py-5">
      <Heading className="font-semibold" level={2}>
        {t("details.integration_info.title")}
      </Heading>
      <div className="grid grid-cols-2 max-md:grid-cols-1 gap-4 border-t py-4">
        {v2 && (
          <div className="flex flex-col gap-6 pt-4">
            <Heading className="font-semibold" level={3}>
              {t("details.integration_info.uitid_v1")}
            </Heading>
            <div className="flex">
              <Heading className="font-semibold flex min-w-[5rem]" level={4}>
                {t("details.integration_info.test")}
              </Heading>
              <p>{id}</p>
            </div>
            <div className="flex">
              <Heading className="font-semibold min-w-[5rem]" level={4}>
                {t("details.integration_info.live")}
              </Heading>
              <div className="flex gap-1 align-center">
                <StatusLight status={status} />
                <Heading level={5}>
                  {t(`integrations.status.${status}`)}
                </Heading>
              </div>
            </div>
            {status === "pending_approval_payment" && (
              <div className="flex flex-col gap-4">
                <p className="ml-[5rem] max-w-[25rem]">
                  {t("details.integration_info.status_alert")}
                  <Link
                    className="pl-1"
                    href={
                      "https://docs.publiq.be/docs/uitdatabank/entry-api/requirements-before-going-live"
                    }
                  >
                    {t("details.integration_info.link")}
                  </Link>
                </p>
                <Button className="ml-[5rem] self-start">
                  {t("details.integration_info.action_status")}
                </Button>
              </div>
            )}
          </div>
        )}
        <div className="flex flex-col gap-6 pt-4">
          <Heading className="font-semibold" level={3}>
            {t("details.integration_info.uitid_v2")}
          </Heading>
          {v1 ? (
            <div className="flex flex-col gap-4">
              <p>{t("details.integration_info.uitid_alert")}</p>
              <Button className="self-start">
                {t("details.integration_info.action_uitid")}
              </Button>
            </div>
          ) : (
            <div>
              <div className="flex">
                <Heading className="font-semibold flex min-w-[5rem]" level={4}>
                  {t("details.integration_info.test")}
                </Heading>
                <p>{id}</p>
              </div>
              <div className="flex">
                <Heading className="font-semibold min-w-[5rem]" level={4}>
                  {t("details.integration_info.live")}
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
                    {t("details.integration_info.status_alert")}
                  </p>
                  <Button className="ml-[5rem] self-start">
                    {t("details.integration_info.action_status")}
                  </Button>
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
