import React from "react";
import { SupportTypes } from "../../Components/SupportTypes";
import { Heading } from "../../Components/Heading";
import { useTranslation } from "react-i18next";
import { Page } from "../../Components/Page";

export type SupportProps = {
  email: string;
  slackStatus: string;
};

const Index = (props: SupportProps) => {
  const { t } = useTranslation();
  return (
    <Page>
      <div className="flex flex-col w-full gap-5 py-8">
        <Heading level={1}>{t("support.title")}</Heading>
        <p>{t("support.description")}</p>
      </div>
      <SupportTypes {...props} />
    </Page>
  );
};

export default Index;
