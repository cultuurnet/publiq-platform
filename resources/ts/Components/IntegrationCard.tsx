import React from "react";
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
import type { IconSearchApi } from "./icons/IconSearchApi";
import { ActivationRequest } from "./ActivationRequest";
import { IntegrationType } from "../types/IntegrationType";
import { CopyText } from "./CopyText";
import type { Credentials } from "./Integrations/Detail/Credentials";
import { KeyVisibility } from "../types/KeyVisibility";
import type { Integration } from "../types/Integration";
import { Alert } from "./Alert";
import { classNames } from "../utils/classNames";

type Props = Integration &
  Credentials & {
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
  legacyTestConsumer,
  legacyProdConsumer,
  testClient,
  prodClient,
  keyVisibility,
  onEdit,
}: Props) => {
  const { t } = useTranslation();

  const integrationTypesInfo = useIntegrationTypesInfo();

  const auth0TestClientWithLabels = [
    {
      label: "details.credentials.client_id",
      value: testClient?.clientId,
    },
    {
      label: "details.credentials.client_secret",
      value: testClient?.clientSecret,
    },
  ];

  const auth0ProdClientWithLabels = [
    {
      label: "details.credentials.client_id",
      value: prodClient?.clientId,
    },
    {
      label: "details.credentials.client_secret",
      value: prodClient?.clientSecret,
    },
  ];

  const hasAnyCredentials = Boolean(
    legacyTestConsumer || legacyProdConsumer || testClient || prodClient
  );
  const CardIcon = integrationTypesInfo.find((i) => i.type === type)?.Icon as
    | typeof IconSearchApi
    | undefined;

  const credentials = (
    <>
      {type !== IntegrationType.Widgets &&
        keyVisibility !== KeyVisibility.v1 && (
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
      {type !== IntegrationType.Widgets &&
        keyVisibility !== KeyVisibility.v2 &&
        legacyTestConsumer && (
          <section className="flex-1 flex max-md:flex-col max-md:items-start md:items-center gap-3">
            <Heading
              level={5}
              className="font-semibold min-w-[10rem] self-start"
            >
              {t("integrations.test")}
            </Heading>
            <span className="flex items-center whitespace-nowrap">
              {t("details.credentials.api_key")}
            </span>
            <CopyText>{legacyTestConsumer.apiKey}</CopyText>
          </section>
        )}

      {type === IntegrationType.Widgets &&
        status !== IntegrationStatus.Active && (
          <section className="flex-1 flex max-md:flex-col max-md:items-start md:items-center justify-start gap-3">
            <Heading level={5} className="font-semibold min-w-[10rem]">
              {t("integrations.test")}
            </Heading>
            <OpenWidgetBuilderButton type={type} id={id} />
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
              <OpenWidgetBuilderButton type={type} id={id} />
            )}
            {keyVisibility !== KeyVisibility.v1 &&
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
      {keyVisibility !== KeyVisibility.v2 &&
        legacyProdConsumer &&
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
              <StatusLight status={status} />
              <div className="flex gap-1 max-md:flex-col max-md:items-start">
                <span className="flex items-center whitespace-nowrap">
                  {t("details.credentials.api_key")}
                </span>
                <CopyText>{legacyProdConsumer.apiKey}</CopyText>
              </div>
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
            className="text-publiq-blue"
          >
            {t("integrations.documentation.action_title", {
              product: t(`integrations.products.${type}`),
            })}
          </Link>
          {type === IntegrationType.EntryApi && (
            <Link
              href="https://docs.publiq.be/docs/uitdatabank/entry-api%2Frequirements-before-going-live"
              className="text-publiq-blue"
            >
              {t("integrations.documentation.requirements")}
            </Link>
          )}
        </div>
      </section>
    </>
  );

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
      <div
        className={classNames(
          "flex flex-col gap-4 mx-8 my-6 items-stretch ",
          hasAnyCredentials && "min-h-[10rem]"
        )}
      >
        {hasAnyCredentials ? (
          credentials
        ) : (
          <Alert variant={"info"}>
            {t("integrations.pending_credentials")}
          </Alert>
        )}
      </div>
    </Card>
  );
};
