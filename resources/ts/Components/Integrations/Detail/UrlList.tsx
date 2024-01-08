import React, { ComponentProps, useEffect, useMemo, useState } from "react";
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
import { random } from "lodash";

type ChangedIntegrationUrl = IntegrationUrl & {
  changed: boolean;
};

export type NewIntegrationUrl = IntegrationUrl;

type UrlListProps = {
  type: IntegrationUrlType;
  urls: ChangedIntegrationUrl[];
  newIntegrationUrls: NewIntegrationUrl[];
  errors: Record<string, string | undefined>;
  onChangeData: (value: ChangedIntegrationUrl[]) => void;
  onChangeNewUrl: (value: NewIntegrationUrl & { id: string }) => void;
  onDeleteNewUrl: (fields?: string[], id?: string) => void;
  onDeleteExistingUrl: (urlId: IntegrationUrl["id"]) => void;
  disabled?: boolean;
} & ComponentProps<"div">;

export const UrlList = ({
  type,
  urls,
  newIntegrationUrls,
  errors,
  onChangeData,
  onChangeNewUrl,
  onDeleteNewUrl,
  onDeleteExistingUrl,
  className,
  disabled,
}: UrlListProps) => {
  const { t } = useTranslation();

  const [toBeDeletedId, setToBeDeletedId] = useState("");
  // const [toBeDeletedExistingUrlFieldId, setToBeDeletedExistingUrlFieldId] =
  useState("");

  const [toBeDeletedField, setToBeDeletedField] = useState("");
  const [toBeDeletedUrlId, setToBeDeletedUrlId] = useState("");
  const [isDialogVisible, setIsDialogVisible] = useState(false);

  const randomNumber = random(0, 1000);

  const testUrls = useMemo(
    () => urls.filter((url) => url.environment === Environment.Test),
    [urls]
  );

  const prodUrls = useMemo(
    () => urls.filter((url) => url.environment === Environment.Prod),
    [urls]
  );

  const [newUrlFields, setNewUrlFields] = useState<string[]>([]);

  const handleDeleteNewUrl = (field?: string, id?: string) => {
    const updatedFields = newUrlFields.filter((item) => item !== field);
    setNewUrlFields(updatedFields);
    onDeleteNewUrl(updatedFields, id);
  };

  const handleClearField = (fieldId: string) => {
    const element = document.getElementById(fieldId) as HTMLInputElement | null;
    if (element) {
      element.value = "";
      element.setAttribute("changed", "true");
    }
  };

  const modifiedUrls = useMemo(
    () => [
      {
        urls: testUrls,
        env: Environment.Test,
        addNewUrlField(newUrlFieldId: string) {
          setNewUrlFields([...newUrlFields, newUrlFieldId]);
        },
      },
      {
        urls: prodUrls,
        env: Environment.Prod,
        addNewUrlField(newUrlFieldId: string) {
          setNewUrlFields([...newUrlFields, newUrlFieldId]);
        },
      },
    ],
    [testUrls, prodUrls, newUrlFields]
  );

  useEffect(() => {
    if (newIntegrationUrls.length === 0) {
      setNewUrlFields([]);
    }
  }, [newIntegrationUrls]);

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
                  elementId={url.id}
                  className={`${type + option.env}`}
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
                        defaultValue={url.url}
                        className="md:min-w-[40rem]"
                        inputId={type + option.env}
                        onChange={(e) => {
                          if (
                            // if it's a cleared field
                            document
                              .getElementById(type + option.env)
                              ?.getAttribute("changed")
                          ) {
                            onChangeNewUrl({
                              environment: option.env,
                              url: e.target.value,
                              type,
                              id: `${type + option.env}`,
                            });
                          } else {
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
                            );
                          }
                        }}
                      />
                      <ButtonIcon
                        icon={faTrash}
                        onClick={() => {
                          setToBeDeletedId(url.id);
                          setIsDialogVisible(true);
                        }}
                        className="text-icon-gray"
                      />
                    </div>
                  }
                />
              ))}
              <div className="flex flex-col gap-2">
                {newUrlFields.map(
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
                              onChange={(e) =>
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
                                setToBeDeletedField(field);
                                setIsDialogVisible(true);
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
                    option.addNewUrlField(`${option.env + randomNumber}`);
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
                      //disabled={disabled}
                      onChange={(e) =>
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
                        setToBeDeletedUrlId(`${type + option.env}`);
                        handleClearField(`${type + option.env}`);
                        setIsDialogVisible(true);
                      }}
                    />
                  </div>
                }
              />
              {newUrlFields.map(
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
                            onChange={(e) =>
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
                              setToBeDeletedField(field);
                              setIsDialogVisible(true);
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
                    option.addNewUrlField(`${option.env + randomNumber}`);
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
        isVisible={isDialogVisible}
        onClose={() => {
          setToBeDeletedId("");
          setIsDialogVisible(false);
        }}
        title={t("details.integration_settings.delete.title")}
        question={t("details.integration_settings.delete.question")}
        onConfirm={() => {
          if (toBeDeletedId) {
            onDeleteExistingUrl(toBeDeletedId);
            setToBeDeletedId("");
          }
          setIsDialogVisible(false);
          handleDeleteNewUrl(toBeDeletedField, toBeDeletedUrlId);
        }}
        onCancel={() => {
          setToBeDeletedId("");
          setIsDialogVisible(false);
        }}
      />
    </div>
  );
};
