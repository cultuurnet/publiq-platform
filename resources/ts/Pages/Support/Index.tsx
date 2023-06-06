import React, { ReactNode } from "react";
import Layout from "../../Shared/Layout";
import { SupportTypes } from "../../Shared/SupportTypes";
import { Heading } from "../../Shared/Heading";
import { useTranslation } from "react-i18next";

const Index = () => {
  const { t } = useTranslation();
  return (
    <>
      <div className="flex flex-col max-xl:px-14 xl:px-60">
        <div className="flex flex-col gap-5 py-8">
        <Heading level={1}>{t("support.title")}</Heading>
        <p>{t("support.description")}</p>
        </div>
        <SupportTypes />
      </div>
    </>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
