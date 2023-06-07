import React, { ReactNode, useCallback } from "react";
import { router } from "@inertiajs/react";
import { Heading } from "../../Shared/Heading";
import Layout from "../../Shared/Layout";
import { LinkButton } from "../../Shared/LinkButton";
import { Input } from "../../Shared/Input";
import { debounce } from "lodash";
import { faSearch } from "@fortawesome/free-solid-svg-icons";
import { useTranslateRoute } from "../../hooks/useTranslateRoute";
import { Pagination } from "../../Shared/Pagination";
import { useTranslation } from "react-i18next";
import { IntegrationCard } from "../../Shared/IntegrationCard";

export type Integration = {
  id: string;
  type: string;
  name: string;
  description: string;
  subscriptionId: string;
  status: string;
};

type PaginationInfo = {
  paginationInfo: { links: string[]; totalItems: number };
};

type Props = {
  integrations: Integration[];
} & PaginationInfo;

const Index = ({ integrations, paginationInfo }: Props) => {
  const { t } = useTranslation();
  const translateRoute = useTranslateRoute();

  const searchParams = new URLSearchParams(document.location.search);
  const searchFromUrl = searchParams.get("search");

  const handleChangeSearchInput = useCallback(
    (e: React.ChangeEvent<HTMLInputElement>): void => {
      debounce(() => {
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
      }, 250)();
    },
    [translateRoute]
  );

  return (
    <section className="flex flex-col items-center w-full pt-6 px-4 :max-sm:px-2 gap-7 min-w-[40rem] max-w-7xl">
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
        <LinkButton href={translateRoute("/integrations/new")}>
          {t("integrations.add")}
        </LinkButton>
      </div>

      {integrations.length === 0 ? (
        <div className="flex flex-col gap-5">
          {t("integrations.no_results_found")}
        </div>
      ) : (
        <div>
          {t("integrations.results_found", {
            count: paginationInfo.totalItems,
          })}
        </div>
      )}
      {integrations.length > 0 && (
        <ul className="flex flex-col w-full gap-5">
          {integrations.map((integration) => (
            <li className="flex w-full" key={integration.id}>
              <IntegrationCard {...integration} />
            </li>
          ))}
        </ul>
      )}

      <Pagination links={paginationInfo.links} />
    </section>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
