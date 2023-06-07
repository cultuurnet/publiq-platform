import React, { ReactNode, useCallback } from "react";
import { router } from "@inertiajs/react";
import { Heading } from "../../Shared/Heading";
import Layout from "../../Shared/Layout";
import { LinkButton } from "../../Shared/LinkButton";
import { Input } from "../../Shared/Input";
import { debounce } from "lodash";
import { faPencil, faSearch, faTrash } from "@fortawesome/free-solid-svg-icons";
import { Card } from "../../Shared/Card";
import { useTranslateRoute } from "../../hooks/useTranslateRoute";
import { Link } from "../../Shared/Link";
import { IconButton } from "../../Shared/IconButton";
import { Pagination } from "../../Shared/Pagination";

type Integration = {
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
        <Heading level={2}>My integrations</Heading>
        <Input
          type="text"
          name="search"
          placeholder="Zoeken ..."
          className="max-w-[30rem]"
          iconBack={faSearch}
          defaultValue={searchFromUrl ?? ""}
          onChange={handleChangeSearchInput}
        />
        <LinkButton href={translateRoute("/integrations/new")}>
          Integratie toevoegen
        </LinkButton>
      </div>

      {integrations.length === 0 ? (
        <div className="flex flex-col gap-5">No integrations found</div>
      ) : (
        <div>Found {paginationInfo.totalItems} results:</div>
      )}
      {integrations.length > 0 && (
        <ul className="flex flex-col w-full gap-5">
          {integrations.map((integration) => (
            <li className="flex w-full" key={integration.id}>
              <Card
                title={
                  <div className="inline-flex w-full justify-between">
                    <div className="inline-flex gap-3 items-center">
                      <Heading level={2}>{integration.name}</Heading>
                      <span>{integration.type}</span>
                    </div>
                    <div className="inline-flex gap-3">
                      <IconButton icon={faPencil} />
                      <IconButton icon={faTrash} color="red" />
                    </div>
                  </div>
                }
                description={integration.description}
                className="w-full"
              >
                <div className="flex flex-col gap-2">
                  <section className="inline-flex gap-3 items-center">
                    <Heading level={3}>Test</Heading>
                    <span className="bg-gray-200 p-1">{integration.id}</span>
                  </section>
                  <section className="inline-flex gap-3 items-center">
                    <Heading level={3}>Live</Heading>
                    <span>Niet actief</span>
                  </section>
                  <section className="inline-flex gap-3 items-center">
                    <Heading level={3}>Documentatie</Heading>
                    <Link href="#">Kijk hier</Link>
                  </section>
                </div>
              </Card>
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
