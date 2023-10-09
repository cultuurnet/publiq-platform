import React, { useState } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonIcon } from "../../ButtonIcon";
import { faPlus, faTrash } from "@fortawesome/free-solid-svg-icons";
import { IntegrationUrl } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { capitalize } from "../../../utils/capitalize";
import { RadioButtonGroup } from "../../RadioButtonGroup";
import { ButtonPrimary } from "../../ButtonPrimary";
import { ButtonSecondary } from "../../ButtonSecondary";
import { Environment } from "../../../types/Environment";
import { QuestionDialog } from "../../QuestionDialog";
import { Dialog } from "../../Dialog";

type ChangedIntegrationUrl = IntegrationUrl & {
  changed: boolean;
};

type NewUrl = { environment: Environment; url: string };
type UrlListProps = {
  type: IntegrationUrlType;
  urls: ChangedIntegrationUrl[];
  newUrl: NewUrl;
  onChangeData: (value: ChangedIntegrationUrl[]) => void;
  onChangeNewUrl: (value: NewUrl) => void;
  onDelete: (urlId: IntegrationUrl["id"]) => void;
  isMobile: boolean;
  isDisabled: boolean;
  isAddVisible?: boolean;
  onSave: () => void;
};
export const UrlList = ({
  type,
  urls,
  newUrl,
  onChangeData,
  onChangeNewUrl,
  onDelete,
  isMobile,
  isAddVisible = true,
  onSave,
}: UrlListProps) => {
  const { t } = useTranslation();

  const [isAddFormVisible, setIsAddFormVisible] = useState(false);
  const [toBeDeletedId, setToBeDeletedId] = useState("");

  return (
    <>
      <div className="flex items-center gap-2 ">
        <Heading className="font-semibold" level={3}>
          {t(`details.integration_settings.${type}`)}
        </Heading>
        {isAddVisible && (
          <ButtonIcon
            className="flex gap-2 items-center"
            icon={faPlus}
            onClick={() => {
              setIsAddFormVisible(true);
            }}
          ></ButtonIcon>
        )}
      </div>

      <Dialog
        isVisible={isAddFormVisible}
        onClose={() => setIsAddFormVisible(false)}
        isFullscreen={isMobile}
      >
        <div className="flex flex-col gap-2">
          <Heading className="font-semibold" level={3}>
            {t("details.integration_settings.new_url", {
              type: capitalize(type),
            })}
          </Heading>
          <FormElement
            label={`${t("details.integration_settings.environment")}`}
            component={
              <RadioButtonGroup
                name="integrationType"
                className="md:min-w-[32rem]"
                options={[
                  {
                    label: t("details.integration_settings.acc"),
                    value: Environment.Acc,
                  },
                  {
                    label: t("details.integration_settings.test"),
                    value: Environment.Test,
                  },
                  {
                    label: t("details.integration_settings.prod"),
                    value: Environment.Prod,
                  },
                ]}
                value={newUrl.environment}
                onChange={(value) =>
                  onChangeNewUrl({
                    ...newUrl,
                    environment: value as Environment,
                  })
                }
              />
            }
          />

          <FormElement
            label={`${t("details.integration_settings.url")}`}
            component={
              <Input
                type="text"
                name="url"
                value={newUrl.url}
                onChange={(e) =>
                  onChangeNewUrl({ ...newUrl, url: e.target.value })
                }
              />
            }
          />
          <div className="flex justify-center gap-2 m-5">
            <ButtonPrimary
              className="p-0"
              onClick={() => {
                onSave();
                setIsAddFormVisible(false);
              }}
            >
              {t("details.contact_info.save")}
            </ButtonPrimary>
            <ButtonSecondary onClick={() => setIsAddFormVisible(false)}>
              {t("details.contact_info.cancel")}
            </ButtonSecondary>
          </div>
        </div>
      </Dialog>
      {urls.length > 0 ? (
        urls.map((url) => {
          return (
            <FormElement
              key={url.id}
              label={`${t(`details.integration_settings.${url.environment}`)}`}
              labelPosition={isMobile ? "top" : "left"}
              component={
                <div className="flex gap-2">
                  <Input
                    type="text"
                    name="loginProduction"
                    value={url.url}
                    className="md:min-w-[32rem]"
                    onChange={(e) =>
                      onChangeData(
                        urls.map((url) => {
                          if (url.id === url.id) {
                            return {
                              ...url,
                              url: e.target.value,
                              changed: true,
                            };
                          }
                          return url;
                        })
                      )
                    }
                    disabled={isDisabled}
                  />
                  <ButtonIcon
                    icon={faTrash}
                    onClick={() => setToBeDeletedId(url.id)}
                    className="text-icon-gray"
                  />
                </div>
              }
            />
          );
        })
      ) : (
        <div>{t("details.integration_settings.empty")}</div>
      )}
    </>
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
      ></QuestionDialog>
  );
};
