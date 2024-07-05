import React from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import { StatusLight } from "../../StatusLight";
import { CopyText } from "../../CopyText";
import { ActivationFlow } from "../../ActivationFlow";
import { Alert } from "../../Alert";
import { IntegrationStatus } from "../../../types/IntegrationStatus";
import type { Credentials } from "./Credentials";
import { KeyVisibility } from "../../../types/KeyVisibility";
import type { Integration } from "../../../types/Integration";
import { formatDistanceToNow, type Locale } from "date-fns";
import { nlBE, enUS } from "date-fns/locale";

type Props = Pick<
  Integration,
  "id" | "status" | "subscription" | "type" | "keyVisibility"
> &
  Credentials & { email: string; oldCredentialsExpirationDate: string };

const languageToLocale: { [key: string]: Locale } = {
  nl: nlBE,
  en: enUS,
};

export const CredentialsLegacyAuthConsumers = ({
  legacyTestConsumer,
  legacyProdConsumer,
  id,
  status,
  email,
  subscription,
  type,
  keyVisibility,
  oldCredentialsExpirationDate: oldCredentialsExpirationDateString,
}: Props) => {
  const { t, i18n } = useTranslation();
  const oldCredentialsExpirationDate = new Date(
    oldCredentialsExpirationDateString
  );
  const timeLeft = formatDistanceToNow(oldCredentialsExpirationDate, {
    locale: languageToLocale[i18n.language],
  });

  return (
    <div className="flex w-full max-lg:flex-col gap-6 border-b pb-10 border-gray-300">
      <Heading className="font-semibold lg:min-w-60" level={4}>
        {t("details.credentials.uitid_v1")}
      </Heading>
      <div className="flex flex-col gap-4 min-w-[40rem]">
        <Heading className="font-semibold" level={4}>
          {t("details.credentials.test")}
        </Heading>
        <div className="flex gap-1 max-md:flex-col max-md:items-start">
          <span className="flex items-center whitespace-nowrap">
            {t("details.credentials.api_key")}
          </span>
          {legacyTestConsumer && (
            <CopyText isSecret text={legacyTestConsumer.apiKey} />
          )}
        </div>
        {keyVisibility !== KeyVisibility.v2 && (
          <div className="flex flex-col gap-2">
            <Heading className="font-semibold" level={4}>
              {t("details.credentials.live")}
            </Heading>
            <StatusLight status={status} />
            {legacyProdConsumer && (
              <div className="flex flex-col gap-2">
                {status === IntegrationStatus.Active && (
                  <div className="flex gap-1 max-md:flex-col max-md:items-start">
                    <span className="flex items-center whitespace-nowrap">
                      {t("details.credentials.api_key")}
                    </span>
                    <CopyText isSecret text={legacyProdConsumer.apiKey} />
                  </div>
                )}
                {keyVisibility === KeyVisibility.all &&
                  oldCredentialsExpirationDate && (
                    <Alert variant="info">
                      {t("details.credentials.info", {
                        date: oldCredentialsExpirationDate.toLocaleDateString(),
                        amount: timeLeft,
                      })}
                    </Alert>
                  )}
              </div>
            )}
            {status === IntegrationStatus.Draft &&
              keyVisibility === KeyVisibility.v1 && (
                <ActivationFlow
                  status={status}
                  id={id}
                  subscription={subscription}
                  type={type}
                  email={email}
                />
              )}
          </div>
        )}
      </div>
    </div>
  );
};
