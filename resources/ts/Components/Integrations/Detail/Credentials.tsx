import React, { useMemo } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import type { Integration } from "../../../Pages/Integrations/Index";
import { StatusLight } from "../../StatusLight";
import { ButtonPrimary } from "../../ButtonPrimary";
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

  const auth0TestClient = useMemo(
    () => auth0Clients.find((client) => client.tenant === "test"),
    [auth0Clients]
  );
  const auth0ProdClient = useMemo(
    () => auth0Clients.find((client) => client.tenant === "prod"),
    [auth0Clients]
  );

  const uiTiDv1TestConsumer = useMemo(
    () => uiTiDv1Consumers.find((consumer) => consumer.environment === "test"),
    [uiTiDv1Consumers]
  );

  const uiTiDv1ProdConsumer = useMemo(
    () => uiTiDv1Consumers.find((consumer) => consumer.environment === "prod"),
    [uiTiDv1Consumers]
  );

  return (
    <>
      <div>
        {uiTiDv1TestConsumer && (
          <div className="flex w-full max-lg:flex-col gap-6 border-b pb-10 border-gray-300">
            <Heading className="font-semibold lg:min-w-60" level={4}>
              {t("details.credentials.uitid_v1")}
            </Heading>
            <div className="flex flex-col gap-6 min-w-[40rem]">
              <div>
                <Heading className="font-semibold flex" level={4}>
                  {t("details.credentials.test")}
                </Heading>
                <p>Api key: {uiTiDv1TestConsumer?.apiKey}</p>
              </div>
              <div>
                <Heading className="font-semibold" level={4}>
                  {t("details.credentials.live")}
                </Heading>
                {status === "active" && (
                  <p>Api key: {uiTiDv1ProdConsumer?.apiKey}</p>
                )}
                <div className="flex gap-1 align-center">
                  {status !== "active" && (
                    <StatusLight
                      status={status}
                      id={id}
                      subscription={subscription}
                      type={type}
                      email={email}
                    />
                  )}
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
      <div className="flex w-full max-lg:flex-col gap-6">
        <Heading className="font-semibold lg:min-w-60" level={4}>
          {t("details.credentials.uitid_v2")}
        </Heading>
        {uiTiDv1TestConsumer && !auth0ProdClient ? (
          <div className="flex flex-col flex-1 gap-4">
            <p>{t("details.credentials.uitid_alert")}</p>
            <ButtonPrimary
              className="self-start"
              onClick={handleDistributeAuth0Clients}
            >
              {t("details.credentials.action_uitid")}
            </ButtonPrimary>
          </div>
        ) : (
          <div className="flex flex-col flex-1 gap-4">
            <div className="flex flex-col">
              <Heading className="font-semibold flex min-w-[5rem]" level={4}>
                {t("details.credentials.test")}
              </Heading>
              <div className="flex w-full">
                Client id: {auth0TestClient?.clientId}
              </div>
              <div className="flex w-full">
                Client secret: {auth0TestClient?.clientSecret}
              </div>
            </div>
            <div className="flex flex-col">
              <Heading className="font-semibold min-w-[5rem]" level={4}>
                {t("details.credentials.live")}
              </Heading>
              {status === "active" && (
                <>
                  <div className="flex w-full">
                    Client id: {auth0ProdClient?.clientId}
                  </div>
                  <div className="flex w-full">
                    Client secret: {auth0ProdClient?.clientSecret}
                  </div>
                </>
              )}
              <div className="flex gap-1 align-center">
                {status !== "active" && (
                  <StatusLight
                    status={status}
                    id={id}
                    subscription={subscription}
                    type={type}
                    email={email}
                  />
                )}
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
};
