import React, { ReactNode, useCallback, useEffect, useState } from "react";
import { router, usePage } from "@inertiajs/react";
import { Heading } from "../../Shared/Heading";
import Layout from "../../Shared/Layout";
import { LinkButton } from "../../Shared/LinkButton";
import { useTranslation } from "react-i18next";
import { Input } from "../../Shared/Input";
import { debounce } from "lodash";

type Props = {
  integrations: { id: string; name: string }[];
};

const Index = ({ integrations }: Props) => {
  const { t } = useTranslation();

  const searchParams = new URLSearchParams(document.location.search);
  const searchFromUrl = searchParams.get("search");

  const handleChangeSearchInput = useCallback(
    (e: React.ChangeEvent<HTMLInputElement>): void => {
      debounce(() => {
        router.get(
          t("pages./integrations") as string,
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
          defaultValue={searchFromUrl ?? ""}
          onChange={handleChangeSearchInput}
        />
        <LinkButton href={t("pages./integrations/new")}>
          Integratie toevoegen
        </LinkButton>
      </div>
      <ul>
        {integrations.map((integration) => (
          <li key={integration.id}>{integration.name}</li>
        ))}
      </ul>
    </section>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
