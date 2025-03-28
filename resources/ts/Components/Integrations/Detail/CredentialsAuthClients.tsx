import React from "react";
import { Heading } from "../../Heading";
import { Trans, useTranslation } from "react-i18next";
import { StatusLight } from "../../StatusLight";
import { ButtonPrimary } from "../../ButtonPrimary";
import { ActivationFlow } from "../../ActivationFlow";
import { IntegrationStatus } from "../../../types/IntegrationStatus";
import type { Credentials } from "./Credentials";
import type { Integration } from "../../../types/Integration";
import { KeyVisibility } from "../../../types/KeyVisibility";
import { router } from "@inertiajs/react";
import { Link } from "../../Link";
import { Alert } from "../../Alert";
import { IntegrationClientCredentials } from "../../IntegrationClientCredential";

type Props = Pick<
  Integration,
  | "id"
  | "status"
  | "subscription"
  | "type"
  | "keyVisibility"
  | "keyVisibilityUpgrade"
> &
  Credentials & { email: string };

export const CredentialsAuthClients = ({
  testClient,
  prodClient,
  id,
  status,
  email,
  subscription,
  type,
  keyVisibility,
  keyVisibilityUpgrade,
}: Props) => {
  const { t } = useTranslation();
  const isKeyVisibilityV1 = keyVisibility === KeyVisibility.v1;

  const handleKeyVisibilityUpgrade = () =>
    router.post(`/integrations/${id}/upgrade`, {
      keyVisibility: KeyVisibility.v2,
    });

  return (
    <div className="flex w-full max-lg:flex-col gap-6">
      <Heading className="font-semibold lg:min-w-60" level={4}>
        {t("details.credentials.uitid_v2")}
      </Heading>
      {isKeyVisibilityV1 ? (
        keyVisibilityUpgrade ? (
          <Alert variant="info">{t("integrations.pending_credentials")}</Alert>
        ) : (
          <div className="flex flex-col flex-1 gap-4">
            <div>
              <Trans
                i18nKey="details.credentials.uitid_alert"
                components={[
                  <Link
                    key={t("details.credentials.uitid_alert")}
                    href={t("details.credentials.uitid_alert_link")}
                    className="text-publiq-blue-dark hover:underline mb-3"
                  />,
                ]}
              />
            </div>

            <ButtonPrimary
              className="self-start"
              onClick={handleKeyVisibilityUpgrade}
            >
              {t("details.credentials.action_uitid")}
            </ButtonPrimary>
          </div>
        )
      ) : (
        <div className="flex flex-col flex-1 gap-4">
          <div className="flex flex-col gap-3">
            <Heading className="font-semibold flex min-w-[5rem]" level={4}>
              {t("details.credentials.test")}
            </Heading>
            <IntegrationClientCredentials
              client={testClient}
              status={status}
              type={type}
              isLive={false}
            />
          </div>
          <div className="flex flex-col gap-2">
            <Heading className="font-semibold min-w-[5rem]" level={4}>
              {t("details.credentials.live")}
            </Heading>
            <StatusLight status={status} />
            {status === IntegrationStatus.Active && (
              <div className="flex flex-col gap-3">
                <IntegrationClientCredentials
                  client={prodClient}
                  status={status}
                  type={type}
                  isLive={true}
                />
              </div>
            )}
            <div className="flex flex-col gap-3 align-center">
              {status === IntegrationStatus.Draft && (
                <ActivationFlow
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
  );
};
