import React from "react";
import { ButtonIcon } from "./ButtonIcon";
import { faPencil } from "@fortawesome/free-solid-svg-icons";
import { Heading } from "./Heading";
import { Card } from "./Card";
import { useTranslation } from "react-i18next";
import { Link } from "./Link";
import { StatusLight } from "./StatusLight";
import { IntegrationStatus } from "../types/IntegrationStatus";
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
import { usePolling } from "../hooks/usePolling";
import { ButtonSecondary } from "./ButtonSecondary";
import { usePageProps } from "../hooks/usePageProps";
import {
  IntegrationClientCredential,
  IntegrationClientCredentials,
} from "./IntegrationClientCredential";

type Props = Integration &
  Credentials & {
    onEdit: (id: string) => void;
  };

const productTypeToPath = {
  uitpas: "/uitpas/getting-started",
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

  const handleOpenWidgetBuilder = () => {
    window.open(`/integrations/${id}/widget`, "_blank");
  };

  return (
    <ButtonSecondary
      onClick={handleOpenWidgetBuilder}
      className="flex self-start"
    >
      {t("integrations.open_widget")}
    </ButtonSecondary>
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
  keycloakTestClient,
  keycloakProdClient,
  keyVisibility,
  onEdit,
}: Props) => {
  const { t } = useTranslation();

  const integrationTypesInfo = useIntegrationTypesInfo();
  const hasAnyCredentials = Boolean(
    legacyTestConsumer ||
      legacyProdConsumer ||
      testClient ||
      prodClient ||
      keycloakProdClient ||
      keycloakProdClient
  );

  usePolling(!hasAnyCredentials, { only: ["credentials"] });
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
              <IntegrationClientCredentials
                client={testClient}
                keycloakClient={keycloakTestClient}
                status={status}
                type={type}
                isLive={false}
              />
            </div>
          </section>
        )}
      {type !== IntegrationType.Widgets &&
        keyVisibility !== KeyVisibility.v2 &&
        legacyTestConsumer && (
          <section className="flex max-md:flex-col max-md:items-start md:items-center gap-3">
            <Heading
              level={5}
              className={classNames(
                keyVisibility === KeyVisibility.all &&
                  "max-md:hidden invisible",
                "font-semibold min-w-[10rem]"
              )}
            >
              {t("integrations.test")}
            </Heading>
            <span className="flex items-center whitespace-nowrap">
              {t("details.credentials.api_key")}
            </span>
            <CopyText isSecret text={legacyTestConsumer.apiKey} />
          </section>
        )}
      {type === IntegrationType.Widgets &&
        status !== IntegrationStatus.Active && (
          <section className="flex-1 flex max-md:flex-col max-md:items-start md:items-center justify-start gap-3">
            <Heading
              level={5}
              className="font-semibold min-w-[10rem] self-start"
            >
              {t("integrations.test")}
            </Heading>
            <OpenWidgetBuilderButton type={type} id={id} />
          </section>
        )}
      {keyVisibility !== KeyVisibility.v1 &&
        type !== IntegrationType.Widgets && (
          <section className="flex-1 inline-flex gap-3 max-md:flex-col max-md:items-start md:items-center">
            <Heading
              className="font-semibold min-w-[10rem] self-start"
              level={5}
            >
              {t("integrations.live")}
            </Heading>
            <div className="flex flex-col gap-3 self-start">
              <StatusLight status={status} />
              <div className="flex flex-col align-center gap-3">
                {status === IntegrationStatus.Draft && (
                  <ActivationRequest id={id} type={type} />
                )}
                {status === IntegrationStatus.Active && (
                  <IntegrationClientCredentials
                    client={prodClient}
                    keycloakClient={keycloakProdClient}
                    status={status}
                    type={type}
                    isLive={true}
                  />
                )}
              </div>
            </div>
          </section>
        )}
      {type === IntegrationType.Widgets && (
        <section className="flex-1 flex max-md:flex-col max-md:items-start md:items-center justify-start gap-3">
          <Heading level={5} className="font-semibold min-w-[10rem] self-start">
            {t("integrations.live")}
          </Heading>
          <div className="flex flex-col gap-3 self-start">
            <StatusLight status={status} />
            {status === IntegrationStatus.Draft && (
              <ActivationRequest id={id} type={type} />
            )}
            {status === IntegrationStatus.Active && (
              <OpenWidgetBuilderButton type={type} id={id} />
            )}
          </div>
        </section>
      )}
      {keyVisibility !== KeyVisibility.v2 &&
        legacyProdConsumer &&
        type !== IntegrationType.Widgets && (
          <section className="flex-1 inline-flex gap-3 max-md:flex-col max-md:items-start md:items-center">
            {(keyVisibility === KeyVisibility.v1 ||
              status === IntegrationStatus.Active) && (
              <Heading
                className={classNames(
                  keyVisibility === KeyVisibility.all &&
                    "max-md:hidden invisible",
                  "font-semibold min-w-[10rem] self-start"
                )}
                level={5}
              >
                {t("integrations.live")}
              </Heading>
            )}
            <div className="flex flex-col gap-2">
              {keyVisibility !== KeyVisibility.all && (
                <StatusLight status={status} />
              )}
              {status === IntegrationStatus.Draft &&
                keyVisibility !== KeyVisibility.all && (
                  <ActivationRequest id={id} type={type} />
                )}
              {status === IntegrationStatus.Active && (
                <div className="flex gap-1 max-md:flex-col max-md:items-start">
                  <span className="flex items-center whitespace-nowrap">
                    {t("details.credentials.api_key")}
                  </span>
                  <CopyText isSecret text={legacyProdConsumer.apiKey} />
                </div>
              )}
            </div>
          </section>
        )}
      <section className="flex-1 inline-flex gap-3 max-md:flex-col max-md:items-start md:items-center">
        <Heading className="font-semibold min-w-[10rem] self-start" level={5}>
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
              href={t("integrations.documentation.action_url", {
                product:
                  "/uitdatabank/entry-api%2Frequirements-before-going-live",
              })}
              className="text-publiq-blue"
            >
              {t("integrations.documentation.requirements")}
            </Link>
          )}
          {type === IntegrationType.UiTPAS && (
            <Link
              href={t("integrations.documentation.action_url", {
                product: "/uitpas/test-dataset",
              })}
              className="text-publiq-blue"
            >
              {t("integrations.documentation.test_dataset")}
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
