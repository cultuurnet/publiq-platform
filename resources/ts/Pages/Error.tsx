import React, { ReactNode } from "react";
import Layout from "../layouts/Layout";
import { Card } from "../Components/Card";
import { useTranslation } from "react-i18next";
import { ButtonSecondary } from "../Components/ButtonSecondary";

const Error = () => {
  const { t } = useTranslation();
  const navigateBack = () => window.history.back();

  return (
    <div className="max-w-[35rem] mt-16 mb-24">
      <Card
        title={t("error.title")}
        textCenter
        contentStyles="flex flex-col gap-3"
      >
        <p className="text-center">{t("error.description")}</p>
        <ButtonSecondary onClick={navigateBack} className="self-center">
          {t("error.back")}
        </ButtonSecondary>
      </Card>
    </div>
  );
};

Error.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Error;
