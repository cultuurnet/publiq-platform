import React from "react";
import type { Credentials, Integration } from "../Pages/Integrations/Index";
import { ButtonIcon } from "./ButtonIcon";
import { faPencil } from "@fortawesome/free-solid-svg-icons";
import { Heading } from "./Heading";
import { Card } from "./Card";
import { useTranslation } from "react-i18next";
import { Link } from "./Link";
import { StatusLight } from "./StatusLight";
import { IntegrationStatus } from "../types/IntegrationStatus";
import { ButtonLinkSecondary } from "./ButtonLinkSecondary";
import {
  integrationIconClasses,
  useIntegrationTypesInfo,
} from "./IntegrationTypes";
import { IconSearchApi } from "./icons/IconSearchApi";
import { ActivationRequest } from "./ActivationRequest";
import { IntegrationType } from "../types/IntegrationType";
import { CopyText } from "./CopyText";
import { Auth0Tenant } from "../types/Auth0Tenant";
import { UiTiDv1Environment } from "../types/UiTiDv1Environment";

type Props = Integration & {
  credentials: Credentials;
  onEdit: (id: string) => void;
};

const productTypeToPath = {
  "entry-api": "/uitdatabank/entry-api/introduction",
  "search-api": "/uitdatabank/search-api/introduction",
  widgets: "/widgets/aan-de-slag",
};

export const OpenWidgetBuilderButton = ({
  id,
  type,
}: Pick<Props, "id" | "type">) => {
  const { t } = useTranslation();
  if (type !== "widgets") {
    return null;
  }

  return (
    <ButtonLinkSecondary
      href={`/integrations/${id}/widget`}
      target="_blank"
      className="flex self-start"
    >
      {t("integrations.open_widget")}
    </ButtonLinkSecondary>
  );
};

export const IntegrationCard = ({
  id,
  name,
  type,
  status,
  credentials,
  onEdit,
}: Props) => {
  const { t } = useTranslation();

  const integrationTypesInfo = useIntegrationTypesInfo();

  const auth0TestClient = credentials.auth0.find(
    (client) =>
      client.integrationId === id && client.tenant === Auth0Tenant.Testing
  );
  const auth0ProdClient = credentials.auth0.find(
    (client) =>
      client.integrationId === id && client.tenant === Auth0Tenant.Production
  );
  const uiTiDv1TestConsumer = credentials.uitidV1.find(
    (client) =>
      client.integrationId === id &&
      client.environment === UiTiDv1Environment.Testing
  );
  const uiTiDv1ProdConsumer = credentials.uitidV1.find(
    (client) =>
      client.integrationId === id &&
      client.environment === UiTiDv1Environment.Production
  );

  const auth0TestClientWithLabels = [
    {
      label: "details.credentials.client_id",
      value: auth0TestClient?.clientId,
    },
    {
      label: "details.credentials.client_secret",
      value: auth0TestClient?.clientSecret,
    },
  ];

  const auth0ProdClientWithLabels = [
    {
      label: "details.credentials.client_id",
      value: auth0ProdClient?.clientId,
    },
    {
      label: "details.credentials.client_secret",
      value: auth0ProdClient?.clientSecret,
    },
  ];

  const CardIcon = integrationTypesInfo.find((i) => i.type === type)?.Icon as
    | typeof IconSearchApi
    | undefined;

  return (
    <Card
      title={name}
      border
      icon={CardIcon && <CardIcon className={integrationIconClasses} />}
      clickableHeading
      id={id}
      iconButton={
        <ButtonIcon
          icon={faPencil}
          className="text-icon-gray"
          onClick={() => onEdit(id)}
        />
      }
    >
      <div className="flex flex-col gap-4 mx-8 my-6 items-stretch min-h-[10rem]">
        {type !== IntegrationType.Widgets && auth0TestClient && (
          <section className="flex-1 flex max-md:flex-col max-md:items-start md:items-center gap-3">
            <Heading
              level={5}
              className="font-semibold min-w-[10rem] self-start"
            >
              {t("integrations.test")}
            </Heading>
            <div className="flex flex-col gap-2">
              <div className="flex flex-col gap-2">
                {auth0TestClientWithLabels.map((client) => (
                  <div
                    key={`${client.label}-${client.value}`}
                    className="flex gap-1 max-md:flex-col max-md:items-start"
                  >
                    <span className="flex items-center whitespace-nowrap">
                      {t(client.label)}
                    </span>
                    <CopyText>{client.value}</CopyText>
                  </div>
                ))}
              </div>
            </div>
          </section>
        )}
        {type !== IntegrationType.Widgets && uiTiDv1TestConsumer && (
          <section className="flex-1 flex max-md:flex-col max-md:items-start md:items-center gap-3">
            <Heading
              level={5}
              className="font-semibold min-w-[10rem] self-start"
            >
              {t("integrations.test")}
            </Heading>
            {uiTiDv1TestConsumer && (
              <CopyText>{uiTiDv1TestConsumer.apiKey}</CopyText>
            )}
          </section>
        )}
        {type === IntegrationType.Widgets &&
          status !== IntegrationStatus.Active && (
            <section className="flex-1 flex max-md:flex-col max-md:items-start md:items-center justify-start gap-3">
              <Heading level={5} className="font-semibold min-w-[10rem]">
                {t("integrations.test")}
              </Heading>
              <OpenWidgetBuilderButton
                type={type}
                id={
                  auth0TestClient
                    ? auth0TestClient.clientId
                    : uiTiDv1TestConsumer!.apiKey
                }
              />
            </section>
          )}

        <section className="flex-1 inline-flex gap-3 max-md:flex-col max-md:items-start md:items-center">
          <Heading className="font-semibold min-w-[10rem] self-start" level={5}>
            {t("integrations.live")}
          </Heading>
          <div className="flex flex-col gap-3 self-start">
            <StatusLight status={status} />
            <div className="flex flex-col align-center gap-3">
              {status === IntegrationStatus.Draft && (
                <ActivationRequest id={id} type={type} />
              )}
              {status === IntegrationStatus.Active && (
                <OpenWidgetBuilderButton
                  type={type}
                  id={
                    auth0ProdClient
                      ? auth0ProdClient.clientId
                      : uiTiDv1ProdConsumer!.apiKey
                  }
                />
              )}
              {auth0ProdClient &&
                status === IntegrationStatus.Active &&
                type !== IntegrationType.Widgets && (
                  <div className="flex flex-col gap-2">
                    {auth0ProdClientWithLabels.map((client) => (
                      <div
                        key={`${client.label}-${client.value}`}
                        className="flex gap-1 max-md:flex-col max-md:items-start"
                      >
                        <span className="flex items-center whitespace-nowrap">
                          {t(client.label)}
                        </span>
                        <CopyText>{client.value}</CopyText>
                      </div>
                    ))}
                  </div>
                )}
            </div>
          </div>
        </section>
        {uiTiDv1ProdConsumer &&
          status === IntegrationStatus.Active &&
          type !== IntegrationType.Widgets && (
            <section className="flex-1 inline-flex gap-3 max-md:flex-col max-md:items-start md:items-center">
              <Heading
                className="font-semibold min-w-[10rem] self-start"
                level={5}
              >
                {t("integrations.live")}
              </Heading>
              <div className="flex flex-col gap-2">
                <CopyText>{uiTiDv1ProdConsumer.apiKey}</CopyText>
              </div>
            </section>
          )}
        <section className="flex-1 inline-flex gap-3 max-md:flex-col max-md:items-start md:items-center">
          <Heading className="font-semibold min-w-[10rem]" level={5}>
            {t("integrations.documentation.title")}
          </Heading>
          <div className="flex flex-col gap-2">
            <Link
              href={t("integrations.documentation.action_url", {
                product: productTypeToPath[type],
              })}
            >
              {t("integrations.documentation.action_title", {
                product: t(`integrations.products.${type}`),
              })}
            </Link>
            {type === "entry-api" && (
              <Link href="https://docs.publiq.be/docs/uitdatabank/entry-api%2Frequirements-before-going-live">
                {t("integrations.documentation.requirements")}
              </Link>
            )}
          </div>
        </section>
      </div>
    </Card>
  );
};
