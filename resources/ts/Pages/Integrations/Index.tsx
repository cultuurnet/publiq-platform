import React, { useMemo, useState } from "react";
import { Heading } from "../../Components/Heading";
import { ButtonLink } from "../../Components/ButtonLink";
import { Input } from "../../Components/Input";
import { debounce } from "lodash";
import { faPlus, faSearch } from "@fortawesome/free-solid-svg-icons";
import { useTranslateRoute } from "../../hooks/useTranslateRoute";
import { Pagination } from "../../Components/Pagination";
import { useTranslation } from "react-i18next";
import { IntegrationCard } from "../../Components/IntegrationCard";
import type { PaginationInfo } from "../../types/PaginationInfo";
import { Page } from "../../Components/Page";
import { QuestionDialog } from "../../Components/QuestionDialog";
import { IconLink } from "../../Components/IconLink";
import { Auth0Tenant } from "../../types/Auth0Tenant";
import { UiTiDv1Environment } from "../../types/UiTiDv1Environment";
import type { Credentials } from "../../types/Credentials";
import type { Integration } from "../../types/Integration";
import { router } from "@inertiajs/react";
import { KeycloakEnvironment } from "../../types/KeycloakEnvironment";
import { WelcomeSection } from "../../Components/WelcomeSection";

type Props = {
  integrations: Integration[];
  credentials: Credentials;
} & PaginationInfo;

const Index = ({ integrations, paginationInfo, credentials }: Props) => {
  const { t } = useTranslation();
  const translateRoute = useTranslateRoute();

  const [isDeleteDialogVisible, setIsDeleteDialogVisible] = useState(false);
  const [toBeDeletedId, setToBeDeletedId] = useState("");

  const searchParams = new URLSearchParams(document.location.search);
  const searchFromUrl = searchParams.get("search");

  const handleChangeSearchInput = debounce(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      router.get(
        translateRoute("/integrations"),
        // Don't append search query param to url if empty
        { search: e.target.value || undefined },
        { preserveScroll: true, preserveState: true }
      );
    },
    250
  );

  const integrationsWithCredentials = useMemo(
    () =>
      integrations.map((integration) => ({
        ...integration,
        credentials: {
          testClient: credentials.auth0.find(
            (client) =>
              client.integrationId === integration.id &&
              client.tenant === Auth0Tenant.Testing
          ),
          prodClient: credentials.auth0.find(
            (client) =>
              client.integrationId === integration.id &&
              client.tenant === Auth0Tenant.Production
          ),
          legacyTestConsumer: credentials.uitidV1.find(
            (client) =>
              client.integrationId === integration.id &&
              client.environment === UiTiDv1Environment.Testing
          ),
          legacyProdConsumer: credentials.uitidV1.find(
            (client) =>
              client.integrationId === integration.id &&
              client.environment === UiTiDv1Environment.Production
          ),
          keycloakTestClient: credentials.keycloak.find(
            (client) =>
              client.integrationId === integration.id &&
              client.environment === KeycloakEnvironment.Testing
          ),
          keycloakProdClient: credentials.keycloak.find(
            (client) =>
              client.integrationId === integration.id &&
              client.environment === KeycloakEnvironment.Testing
          ),
        },
      })),
    [integrations, credentials.auth0, credentials.uitidV1, credentials.keycloak]
  );

  const handleDeleteIntegration = () => {
    router.delete(`/integrations/${toBeDeletedId}`, {
      onFinish: () => setIsDeleteDialogVisible(false),
      preserveScroll: true,
    });
  };

  return (
    <Page>
      <div className="flex max-md:flex-col w-full md:justify-between max-md:gap-3 items-stretch">
        <div className="inline-flex gap-3">
          <Heading level={2}>{t("integrations.title")}</Heading>
          <IconLink
            href={translateRoute("/integrations/new")}
            icon={faPlus}
            className="md:hidden"
          />
        </div>
        <Input
          type="text"
          name="search"
          className="max-w-[30rem] max-lg:max-w-[20rem]"
          iconBack={faSearch}
          defaultValue={searchFromUrl ?? ""}
          onChange={handleChangeSearchInput}
        />
        <ButtonLink
          href={translateRoute("/integrations/new")}
          className="max-md:hidden"
        >
          {t("integrations.add")}
        </ButtonLink>
      </div>

      {integrations.length === 0 && <WelcomeSection />}
      {integrations.length > 0 && (
        <>
          <div className="inline-flex self-start">
            {t("integrations.results_found", {
              count: paginationInfo.totalItems,
            })}
          </div>
          <ul className="flex flex-col w-full gap-9">
            {integrationsWithCredentials.map(
              ({ credentials, ...integration }) => (
                <li className="flex w-full" key={integration.id}>
                  <IntegrationCard
                    {...integration}
                    {...credentials}
                    onEdit={(id) =>
                      router.get(`${translateRoute("/integrations")}/${id}`)
                    }
                  />
                </li>
              )
            )}
          </ul>
        </>
      )}

      <Pagination links={paginationInfo.links} />
      <QuestionDialog
        isVisible={isDeleteDialogVisible}
        onClose={() => {
          setIsDeleteDialogVisible((prev) => !prev);
        }}
        title={t("integrations.dialog.title")}
        question={t("integrations.dialog.delete")}
        onConfirm={handleDeleteIntegration}
        onCancel={() => {
          setIsDeleteDialogVisible(false);
          setToBeDeletedId("");
        }}
      />
    </Page>
  );
};

export default Index;
