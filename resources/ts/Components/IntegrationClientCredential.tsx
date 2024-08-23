import { CopyText } from "./CopyText";
import React from "react";
import { useTranslation } from "react-i18next";

type Props = {
  client: {
    label: string;
    value: string;
  };
};

export const IntegrationClientCredential = ({ client }: Props) => {
  const { t } = useTranslation();
  const clientSecretLabel = t("details.credentials.client_secret");

  return (
    <div className="flex gap-1 max-md:flex-col max-md:items-start">
      <span className="flex items-center whitespace-nowrap">
        {t(client.label)}
      </span>
      {client.value && (
        <CopyText
          isSecret={t(client.label) === clientSecretLabel}
          text={client.value}
        />
      )}
    </div>
  );
};
