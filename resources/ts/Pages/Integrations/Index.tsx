import React, { ReactNode, useState } from "react";
import { router } from "@inertiajs/react";
import { Heading } from "../../Shared/Heading";
import Layout from "../../Shared/Layout";
import { ButtonLink } from "../../Shared/ButtonLink";
import { Input } from "../../Shared/Input";
import { debounce } from "lodash";
import { faSearch } from "@fortawesome/free-solid-svg-icons";
import { useTranslateRoute } from "../../hooks/useTranslateRoute";
import { Pagination } from "../../Shared/Pagination";
import { useTranslation } from "react-i18next";
import { IntegrationCard } from "../../Shared/IntegrationCard";
import { PaginationInfo } from "../../types/PaginationInfo";
import { Page } from "../../Shared/Page";
import { QuestionDialog } from "../../Shared/QuestionDialog";

export type Integration = {
  id: string;
  type: string;
  name: string;
  description: string;
  subscriptionId: string;
  status: string;
};

type Props = {
  integrations: Integration[];
} & PaginationInfo;

const Index = ({ integrations, paginationInfo }: Props) => {
  const { t } = useTranslation();
  const translateRoute = useTranslateRoute();

  const [isDeleteDialogVisible, setIsDeleteDialogVisible] = useState(true);
  const [toBeDeletedId, setToBeDeletedId] = useState("");

  const searchParams = new URLSearchParams(document.location.search);
  const searchFromUrl = searchParams.get("search");

  const handleChangeSearchInput = debounce(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      router.get(
        translateRoute("/integrations"),
        {
          // Don't append search query param to url if empty
          search: e.target.value || undefined,
        },
        {
          preserveState: true,
        }
      );
    },
    250
  );

  const handleDeleteIntegration = () => {
    router.delete(`/integrations/${toBeDeletedId}`, {
      onFinish: () => setIsDeleteDialogVisible(false),
    });
  };

  return (
    <Page>
      <div className="inline-flex w-full justify-between items-center">
        <Heading level={2}>{t("integrations.title")}</Heading>
        <Input
          type="text"
          name="search"
          placeholder={t("integrations.searching") as string}
          className="max-w-[30rem]"
          iconBack={faSearch}
          defaultValue={searchFromUrl ?? ""}
          onChange={handleChangeSearchInput}
        />
        <ButtonLink href={translateRoute("/integrations/new")}>
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
                onDelete={(id) => {
                  setToBeDeletedId(id);
                  setIsDeleteDialogVisible(true);
                }}
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
        question="Wil je deze integratie echt verwijderen?"
        onConfirm={handleDeleteIntegration}
        onCancel={() => {
          setIsDeleteDialogVisible(false);
          setToBeDeletedId("");
        }}
      ></QuestionDialog>
    </Page>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
