import React, { ReactNode } from "react";
import Layout from "../layouts/Layout";
import { Card } from "../Components/Card";
import { Trans, useTranslation } from "react-i18next";
import { ButtonSecondary } from "../Components/ButtonSecondary";
import { Link } from "../Components/Link";

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
        <div className="text-center">
          <Trans
            i18nKey="error.description"
            t={t}
            components={[
              <Link
                key={t("error.description")}
                href="mailto:technical-support@publiq.be"
                className="text-publiq-blue-dark hover:underline"
              />,
            ]}
          />
        </div>
        <ButtonSecondary onClick={navigateBack} className="self-center">
          {t("error.back")}
        </ButtonSecondary>
      </Card>
    </div>
  );
};

Error.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Error;
