import React, { ReactNode, useState } from "react";
import { router } from "@inertiajs/react";
import { Heading } from "../../Shared/Heading";
import Layout from "../../Shared/Layout";
import { ButtonLink } from "../../Shared/ButtonLink";
import { Input } from "../../Shared/Input";
import { debounce } from "lodash";
import { faPlus, faSearch } from "@fortawesome/free-solid-svg-icons";
import { useTranslateRoute } from "../../hooks/useTranslateRoute";
import { Pagination } from "../../Shared/Pagination";
import { useTranslation } from "react-i18next";
import { IntegrationCard } from "../../Shared/IntegrationCard";
import { PaginationInfo } from "../../types/PaginationInfo";
import { Page } from "../../Shared/Page";
import { QuestionDialog } from "../../Shared/QuestionDialog";
import { IconLink } from "../../Shared/IconLink";
import { IntegrationStatus } from "../../types/IntegrationStatus";

export type Integration = {
  id: string;
  type: string;
  name: string;
  description: string;
  subscriptionId: string;
  status: IntegrationStatus;
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
      preserveScroll: true,
    });
  };

  return (
    <Page>
      <div className="flex max-md:flex-col w-full md:justify-between max-md:gap-3 items-center">
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
          placeholder={t("integrations.searching") as string}
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
        question={t("dialog.questions.delete")}
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
