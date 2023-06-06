import React, { ReactNode, useCallback, useEffect, useState } from "react";
import { router, usePage } from "@inertiajs/react";
import { Heading } from "../../Shared/Heading";
import Layout from "../../Shared/Layout";
import { LinkButton } from "../../Shared/LinkButton";
import { useTranslation } from "react-i18next";
import { Input } from "../../Shared/Input";
import { debounce } from "lodash";
import { faSearch } from "@fortawesome/free-solid-svg-icons";
import { Card } from "../../Shared/Card";
import { useTranslateRoute } from "../../hooks/useTranslateRoute";

type Integration = {
  id: string;
  type: string;
  name: string;
  description: string;
  subscriptionId: string;
  status: string;
};

type Props = {
  integrations: Integration[];
};

const Index = ({ integrations }: Props) => {
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
            search: e.target.value,
          },
          {
            preserveState: true,
          }
        );
      }, 250)();
    },
    [t]
  );

  return (
    <section className="flex flex-col w-full pt-6 px-4 :max-sm:px-2 gap-5 min-w-[40rem] max-w-7xl">
      <div className="inline-flex justify-between items-center">
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
      <ul>
        {integrations.map((integration) => (
          <li key={integration.id}>
            <Card
              title={
                <div className="inline-flex gap-3 items-center">
                  <Heading level={2}>{integration.name}</Heading>
                  <span>{integration.type}</span>
                </div>
              }
              description={integration.description}
              className="w-full"
            >
              <section className="inline-flex gap-3">
                <Heading level={3}>Test</Heading>
                <span>{integration.id}</span>
              </section>
              <section>
                <Heading level={3}>Live</Heading>
              </section>
              <section>
                <Heading level={3}>Documentatie</Heading>
              </section>
            </Card>
          </li>
        ))}
      </ul>
    </section>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
