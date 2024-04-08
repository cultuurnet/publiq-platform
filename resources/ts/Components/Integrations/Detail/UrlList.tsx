import type { ComponentProps } from "react";
import React, { Fragment, useState } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonIcon } from "../../ButtonIcon";
import { faTrash, faPlus } from "@fortawesome/free-solid-svg-icons";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { QuestionDialog } from "../../QuestionDialog";
import { classNames } from "../../../utils/classNames";
import { Environment } from "../../../types/Environment";
import type { IntegrationUrl } from "../../../types/IntegrationUrl";

type UrlListProps = {
  type: IntegrationUrlType;
  urls: IntegrationUrl[];
  errors: Record<string, string | undefined>;
  disabled?: boolean;
  onConfirmDeleteUrl: (toDeleteUrlId: string) => void;
  onAddNewUrl: (type: IntegrationUrlType, environment: Environment) => void;
  onChangeUrlValue: (id: string, urlValue: string) => void;
} & ComponentProps<"div">;

export const UrlList = ({
  type,
  urls,
  errors,
  className,
  onConfirmDeleteUrl,
  onAddNewUrl,
  onChangeUrlValue,
}: UrlListProps) => {
  const { t } = useTranslation();

  const testUrls = urls.filter((url) => url.environment === Environment.Test);
  const productionUrls = urls.filter(
    (url) => url.environment === Environment.Prod
  );

  const environmentToUrls = {
    [Environment.Test]: testUrls,
    [Environment.Prod]: productionUrls,
  };

  const [toDeleteUrlId, setToDeleteUrlId] = useState<string>();

  const handleDeleteUrl = (url: IntegrationUrl) => setToDeleteUrlId(url.id);

  return (
    <div
      className={classNames(
        "max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-5",
        className
      )}
    >
      <Heading className="font-semibold" level={4}>
        {t(`details.integration_settings.${type}`)}
      </Heading>

      <div className="flex flex-col gap-5">
        {(
          Object.entries(environmentToUrls) as [Environment, IntegrationUrl[]][]
        ).map(([environment, urls]) => (
          <Fragment key={environment}>
            <label>{t(`details.integration_settings.${environment}`)}</label>
            {urls.map((url, index) => (
              <FormElement
                key={url.id}
                elementId={url.id}
                error={errors[`${url.type}.${url.environment}.${index}`]}
                component={
                  <div className="flex gap-2">
                    <Input
                      type="text"
                      name="url"
                      value={url.url}
                      onChange={(e) => onChangeUrlValue(url.id, e.target.value)}
                      className="md:min-w-[40rem]"
                    />
                    {type !== IntegrationUrlType.Login && (
                      <ButtonIcon
                        icon={faTrash}
                        onClick={() => handleDeleteUrl(url)}
                        className="text-icon-gray"
                      />
                    )}
                  </div>
                }
              />
            ))}
            {(type !== IntegrationUrlType.Login || urls.length === 0) && (
              <ButtonIcon
                icon={faPlus}
                onClick={() => onAddNewUrl(type, environment)}
                className="self-start"
              >
                {t("details.integration_settings.add")}
              </ButtonIcon>
            )}
          </Fragment>
        ))}
      </div>
      <QuestionDialog
        isVisible={!!toDeleteUrlId}
        onClose={() => setToDeleteUrlId(undefined)}
        title={t("details.integration_settings.delete.title")}
        question={t("details.integration_settings.delete.question")}
        onConfirm={() => {
          onConfirmDeleteUrl(toDeleteUrlId!);
          setToDeleteUrlId(undefined);
        }}
        onCancel={() => setToDeleteUrlId(undefined)}
      />
    </div>
  );
};
