import React from "react";
import { Card } from "../Components/Card";
import { Trans, useTranslation } from "react-i18next";
import { ButtonSecondary } from "../../ts/Components/ButtonSecondary";
import { Link } from "../../ts/Components/Link";
import { ErrorImage } from "../Components/ErrorImage";
import { Heading } from "../Components/Heading";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronRight } from "@fortawesome/free-solid-svg-icons";
import { router } from "@inertiajs/react";
import { useTranslateRoute } from "../hooks/useTranslateRoute";

type Props = {
  statusCode: number;
};

const Error = ({ statusCode }: Props) => {
  const { t } = useTranslation();
  const translateRoute = useTranslateRoute();
  const navigateBack = () => {
    // @ts-expect-error We need to check protected property activeVisit
    if (router.activeVisit) {
      window.history.back();
    } else {
      router.get(translateRoute("/"));
    }
  };

  if (statusCode === 404)
    return (
      <div className="flex flex-col gap-5 mt-24 mb-24 text-center items-center h-full">
        <ErrorImage />
        <Heading level={2}>{t("error.404.title")}</Heading>
        <p>{t("error.404.description")}</p>
        <Link href="/" className="text-center">
          {t("error.404.back")}
          <FontAwesomeIcon icon={faChevronRight} size="xs" />
        </Link>
      </div>
    );
  return (
    <div className="max-w-[35rem] mt-16 mb-24">
      <Card
        title={t("error.500.title")}
        textCenter
        contentStyles="flex flex-col gap-3"
      >
        <div className="text-center">
          <Trans
            i18nKey="error.500.description"
            t={t}
            components={[
              <Link
                key={t("error.500.description")}
                href="mailto:technical-support@publiq.be"
                className="text-publiq-blue-dark hover:underline"
              />,
            ]}
          />
        </div>
        <ButtonSecondary onClick={navigateBack} className="self-center">
          {t("error.500.back")}
        </ButtonSecondary>
      </Card>
    </div>
  );
};

export default Error;
