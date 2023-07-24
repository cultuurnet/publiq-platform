import React, { ReactNode } from "react";
import Layout from "../../Components/Layout";
import { SupportTypes } from "../../Components/SupportTypes";
import { Heading } from "../../Components/Heading";
import { useTranslation } from "react-i18next";
import { Page } from "../../Components/Page";

const Index = () => {
  const { t } = useTranslation();
  return (
    <Page>
      <div className="flex flex-col w-full gap-5 py-8">
        <Heading level={1}>{t("support.title")}</Heading>
        <p>{t("support.description")}</p>
      </div>
      <SupportTypes />
    </Page>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
