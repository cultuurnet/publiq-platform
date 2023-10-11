import React, { ComponentProps, useMemo, useState } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonIcon } from "../../ButtonIcon";
import { faTrash } from "@fortawesome/free-solid-svg-icons";
import { IntegrationUrl } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { Environment } from "../../../types/Environment";
import { QuestionDialog } from "../../QuestionDialog";
import { ButtonSecondary } from "../../ButtonSecondary";
import { classNames } from "../../../utils/classNames";

type ChangedIntegrationUrl = IntegrationUrl & {
  changed: boolean;
};

export type NewIntegrationUrl = Omit<IntegrationUrl, "id">;

type UrlListProps = {
  type: IntegrationUrlType;
  urls: ChangedIntegrationUrl[];
  newUrls: NewIntegrationUrl[];
  onChangeData: (value: ChangedIntegrationUrl[]) => void;
  onChangeNewUrl: (value: NewIntegrationUrl) => void;
  onDelete: (urlId: IntegrationUrl["id"]) => void;
} & ComponentProps<"div">;

export const UrlList = ({
  type,
  urls,
  onChangeData,
  onChangeNewUrl,
  onDelete,
  className,
}: UrlListProps) => {
  const { t } = useTranslation();

  const [toBeDeletedId, setToBeDeletedId] = useState("");
  const [isAddTestVisible, setIsAddTestVisible] = useState(false);
  const [isAddProdVisible, setIsAddProdVisible] = useState(false);

  const testUrls = useMemo(
    () => urls.filter((url) => url.environment === Environment.Test),
    [urls]
  );

  const prodUrls = useMemo(
    () => urls.filter((url) => url.environment === Environment.Prod),
    [urls]
  );

  const modifiedUrls = [
    {
      urls: testUrls,
      env: Environment.Test,
      visible: isAddTestVisible,
      changeVisibility(param: boolean) {
        setIsAddTestVisible(param);
      },
    },
    {
      urls: prodUrls,
      env: Environment.Prod,
      visible: isAddProdVisible,
      changeVisibility(param: boolean) {
        setIsAddProdVisible(param);
      },
    },
  ];

  return (
    <div
      className={classNames(
        "max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-5 py-10",
        className
      )}
    >
      <Heading className="font-semibold" level={3}>
        {t(`details.integration_settings.${type}`)}
      </Heading>
      <div className="flex flex-col gap-5">
        {modifiedUrls.map((option) =>
          option.urls.length > 0 ? (
            <div key={option.env} className="flex flex-col gap-2">
              {option.urls.map((url) => (
                <FormElement
                  key={url.id}
                  label={`${t(
                    `details.integration_settings.${url.environment}`
                  )}`}
                  component={
                    <div className="flex gap-2">
                      <Input
                        type="text"
                        name="url"
                        value={url.url}
                        className="md:min-w-[32rem]"
                        onChange={(e) =>
                          onChangeData(
                            urls.map((urlItem) => {
                              if (urlItem.id === url.id) {
                                return {
                                  ...urlItem,
                                  url: e.target.value,
                                  changed: true,
                                };
                              }
                              return urlItem;
                            })
                          )
                        }
                      />
                      {type !== IntegrationUrlType.Login && (
                        <ButtonIcon
                          icon={faTrash}
                          onClick={() => setToBeDeletedId(url.id)}
                          className="text-icon-gray"
                        />
                      )}
                    </div>
                  }
                />
              ))}
              {option.visible && (
                <div className="flex flex-col">
                  <FormElement
                    label={t("details.integration_settings.new")}
                    className=""
                    component={
                      <Input
                        type="text"
                        name="newUrl"
                        className="md:min-w-[32rem]"
                        onBlur={(e) =>
                          onChangeNewUrl({
                            environment: option.env,
                            url: e.target.value,
                            type,
                          })
                        }
                      />
                    }
                  />
                </div>
              )}
              {type !== IntegrationUrlType.Login && (
                <ButtonSecondary
                  onClick={() => {
                    option.changeVisibility(true);
                  }}
                  className="self-start"
                >
                  {t("details.integration_settings.add")}
                </ButtonSecondary>
              )}
            </div>
          ) : (
            <FormElement
              key={option.env}
              label={`${t(`details.integration_settings.${option.env}`)}`}
              component={
                <div className="flex gap-2">
                  <Input
                    type="text"
                    name="url"
                    className="md:min-w-[32rem]"
                    onChange={(e) =>
                      onChangeNewUrl({
                        environment: option.env,
                        url: e.target.value,
                        type,
                      })
                    }
                  />
                </div>
              }
            />
          )
        )}
      </div>
      <QuestionDialog
        isVisible={!!toBeDeletedId}
        onClose={() => {
          setToBeDeletedId("");
        }}
        title={t("details.integration_settings.delete.title")}
        question={t("details.integration_settings.delete.question")}
        onConfirm={() => onDelete(toBeDeletedId)}
        onCancel={() => {
          setToBeDeletedId("");
        }}
      />
    </div>
  );
};
