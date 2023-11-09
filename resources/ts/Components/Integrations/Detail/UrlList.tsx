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

export type NewIntegrationUrl = IntegrationUrl;

type UrlListProps = {
  type: IntegrationUrlType;
  urls: ChangedIntegrationUrl[];
  newUrls: NewIntegrationUrl[];
  errors: Record<string, string | undefined>;
  onChangeData: (value: ChangedIntegrationUrl[]) => void;
  onChangeNewUrl: (value: NewIntegrationUrl & { id: string }) => void;
  onDeleteNewUrl: (fields?: string[], id?: string) => void;
  onDelete: (urlId: IntegrationUrl["id"]) => void;
} & ComponentProps<"div">;

export const UrlList = ({
  type,
  urls,
  errors,
  onChangeData,
  onChangeNewUrl,
  onDeleteNewUrl,
  onDelete,
  className,
}: UrlListProps) => {
  const { t } = useTranslation();

  const [toBeDeletedId, setToBeDeletedId] = useState("");

  const testUrls = useMemo(
    () => urls.filter((url) => url.environment === Environment.Test),
    [urls]
  );

  const prodUrls = useMemo(
    () => urls.filter((url) => url.environment === Environment.Prod),
    [urls]
  );

  const [fields, setFields] = useState<string[]>([]);

  const deleteField = (field?: string, id?: string) => {
    const updatedFields = fields.filter((item) => item !== field);
    setFields(updatedFields);
    onDeleteNewUrl(updatedFields, id);
  };

  const cleanField = (fieldId: string) => {
    const element = document.getElementById(fieldId) as HTMLInputElement | null;
    if (element) {
      element.value = "";
    }
  };

  const modifiedUrls = [
    {
      urls: testUrls,
      env: Environment.Test,
      changeVisibility(param: string) {
        setFields([...fields, param]);
      },
    },
    {
      urls: prodUrls,
      env: Environment.Prod,
      changeVisibility(param: string) {
        setFields([...fields, param]);
      },
    },
  ];

  return (
    <div
      className={classNames(
        "max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-5",
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
              {option.urls.map((url, index) => (
                <FormElement
                  key={url.id}
                  label={
                    index === 0
                      ? `${t(
                          `details.integration_settings.${url.environment}`
                        )}`
                      : ""
                  }
                  component={
                    <div className="flex gap-2">
                      <Input
                        type="text"
                        name="url"
                        value={url.url}
                        className="md:min-w-[40rem]"
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
                      <ButtonIcon
                        icon={faTrash}
                        onClick={() => setToBeDeletedId(url.id)}
                        className="text-icon-gray"
                      />
                    </div>
                  }
                />
              ))}
              <div className="flex flex-col gap-2">
                {fields.map(
                  (field) =>
                    field.startsWith(option.env) && (
                      <FormElement
                        key={field}
                        error={errors[`newIntegrationUrls.${`${field}`}.url`]}
                        component={
                          <div className="flex gap-2">
                            <Input
                              type="text"
                              name="newUrl"
                              className="md:min-w-[40rem]"
                              onBlur={(e) =>
                                onChangeNewUrl({
                                  environment: option.env,
                                  url: e.target.value,
                                  type,
                                  id: field,
                                })
                              }
                            />
                            <ButtonIcon
                              icon={faTrash}
                              onClick={() => {
                                deleteField(field);
                              }}
                              className="text-icon-gray"
                            />
                          </div>
                        }
                      />
                    )
                )}
              </div>
              {type !== IntegrationUrlType.Login && (
                <ButtonSecondary
                  onClick={() => {
                    option.changeVisibility(`${option.env + Math.random()}`);
                  }}
                  className="self-start"
                >
                  {t("details.integration_settings.add")}
                </ButtonSecondary>
              )}
            </div>
          ) : (
            <div key={option.env} className="flex flex-col gap-2">
              <FormElement
                label={`${t(`details.integration_settings.${option.env}`)}`}
                error={
                  errors[`newIntegrationUrls.${`${type + option.env}`}.url`]
                }
                component={
                  <div className="flex gap-2">
                    <Input
                      type="text"
                      inputId={`${type + option.env}`}
                      name="url"
                      className="md:min-w-[40rem]"
                      onBlur={(e) =>
                        onChangeNewUrl({
                          environment: option.env,
                          url: e.target.value,
                          type,
                          id: `${type + option.env}`,
                        })
                      }
                    />
                    <ButtonIcon
                      icon={faTrash}
                      className="text-icon-gray"
                      onClick={() => {
                        deleteField(undefined, `${type + option.env}`);
                        cleanField(`${type + option.env}`);
                      }}
                    />
                  </div>
                }
              />
              {fields.map(
                (field) =>
                  field.startsWith(option.env) && (
                    <FormElement
                      key={field}
                      component={
                        <div className="flex gap-2">
                          <Input
                            type="text"
                            name="newUrl"
                            className="md:min-w-[40rem]"
                            onBlur={(e) =>
                              onChangeNewUrl({
                                environment: option.env,
                                url: e.target.value,
                                type,
                                id: field,
                              })
                            }
                          />
                          <ButtonIcon
                            icon={faTrash}
                            onClick={() => {
                              deleteField(field);
                            }}
                            className="text-icon-gray"
                          />
                        </div>
                      }
                    />
                  )
              )}
              {type !== IntegrationUrlType.Login && (
                <ButtonSecondary
                  onClick={() => {
                    option.changeVisibility(`${option.env + Math.random()}`);
                  }}
                  className="self-start"
                >
                  {t("details.integration_settings.add")}
                </ButtonSecondary>
              )}
            </div>
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
