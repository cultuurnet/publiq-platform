import React, { ReactNode, useState } from "react";
import { router } from "@inertiajs/react";
import { Heading } from "../../Components/Heading";
import Layout from "../../layouts/Layout";
import { ButtonLink } from "../../Components/ButtonLink";
import { Input } from "../../Components/Input";
import { debounce } from "lodash";
import { faPlus, faSearch } from "@fortawesome/free-solid-svg-icons";
import { useTranslateRoute } from "../../hooks/useTranslateRoute";
import { Pagination } from "../../Components/Pagination";
import { useTranslation } from "react-i18next";
import { IntegrationCard } from "../../Components/IntegrationCard";
import { PaginationInfo } from "../../types/PaginationInfo";
import { Page } from "../../Components/Page";
import { QuestionDialog } from "../../Components/QuestionDialog";
import { IconLink } from "../../Components/IconLink";
import { IntegrationStatus } from "../../types/IntegrationStatus";
import { ContactType } from "../../types/ContactType";
import { Environment } from "../../types/Environment";
import { IntegrationUrlType } from "../../types/IntegrationUrlType";
import { IntegrationType } from "../../types/IntegrationType";
import { Values } from "../../types/Values";

type Organization = {
  id: string;
  name: string;
  invoiceMail: string;
  vat: string;
  address: {
    street: string;
    zip: string;
    city: string;
    country: string;
  };
};

export type Contact = {
  id: string;
  integrationId: string;
  email: string;
  type: ContactType;
  firstName: string;
  lastName: string;
};

export type Subscription = {
  id: string;
  name: string;
  description: string;
  category: string;
  integrationType: string;
  currency: string;
  price: number;
  fee: number;
};

export type IntegrationUrl = {
  id: string;
  environment: Environment;
  type: IntegrationUrlType;
  url: string;
};

export type Integration = {
  id: string;
  type: Values<typeof IntegrationType>;
  name: string;
  description: string;
  subscriptionId: string;
  status: IntegrationStatus;
  contacts: Contact[];
  organization?: Organization;
  subscription: Subscription;
  urls: IntegrationUrl[];
  hasCredentials: { v1: boolean; v2: boolean };
};

type Props = {
  integrations: Integration[];
} & PaginationInfo;

const Index = ({ integrations, paginationInfo }: Props) => {
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
      <div className="inline-flex self-start">
        {t("integrations.results_found", {
          count: paginationInfo.totalItems,
        })}
      </div>
      {integrations.length > 0 && (
        <ul className="flex flex-col w-full gap-5">
          {integrations.map((integration) => (
            <li className="flex w-full" key={integration.id}>
              <IntegrationCard
                {...integration}
                onEdit={(id) =>
                  router.get(`${translateRoute("/integrations")}/${id}`)
                }
              />
            </li>
          ))}
        </ul>
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

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
