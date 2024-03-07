import React from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import type { Integration } from "../../../Pages/Integrations/Index";
import { StatusLight } from "../../StatusLight";
import { ButtonPrimary } from "../../ButtonPrimary";
import { Link } from "../../Link";
import { router } from "@inertiajs/react";

type Props = Integration & {
  email: string;
};

export const Credentials = ({
  id,
  status,
  email,
  subscription,
  type,
  uiTiDv1Consumers,
  auth0Clients,
}: Props) => {
  const { t } = useTranslation();

  const handleDistributeAuth0Clients = () => {
    router.post(`/integrations/${id}/auth0-clients`);
  };

  return (
    <>
      <div>
        {auth0Clients.length > 0 && (
          <div className="w-full max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6 border-b pb-10 border-gray-300">
            <Heading className="font-semibold" level={4}>
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
                  <StatusLight
                    status={status}
                    id={id}
                    subscription={subscription}
                    type={type}
                    email={email}
                  />
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
      <div className="w-full max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6">
        <Heading className="font-semibold" level={4}>
          {t("details.credentials.uitid_v2")}
        </Heading>
        {auth0Clients.length === 0 ? (
          <div className="flex flex-col gap-4">
            <p>{t("details.credentials.uitid_alert")}</p>
            <ButtonPrimary
              className="self-start"
              onClick={handleDistributeAuth0Clients}
            >
              {t("details.credentials.action_uitid")}
            </ButtonPrimary>
          </div>
        ) : (
          <div className="flex flex-col gap-4">
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
                <StatusLight
                  status={status}
                  id={id}
                  subscription={subscription}
                  type={type}
                  email={email}
                />
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
};
