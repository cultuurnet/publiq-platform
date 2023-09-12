import React, { useState } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonIcon } from "../../ButtonIcon";
import { faPlus } from "@fortawesome/free-solid-svg-icons";
import { IntegrationUrl } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { capitalize } from "../../../utils/capitalize";
import { RadioButtonGroup } from "../../RadioButtonGroup";
import { ButtonPrimary } from "../../ButtonPrimary";
import { ButtonSecondary } from "../../ButtonSecondary";
import { Environment } from "../../../types/Environment";

type ChangedIntegrationUrl = IntegrationUrl & {
  changed: boolean;
};
type UrlListProps = {
  type: IntegrationUrlType;
  urls: ChangedIntegrationUrl[];
  newUrl: { environment: Environment; url: string };
  onChangeData: (value: ChangedIntegrationUrl[]) => void;
  onChangeNewUrlEnvironment: (value: Environment) => void;
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
  onChangeNewUrlEnvironment,
  isMobile,
  isDisabled,
  isAddVisible = true,
  onSave,
}: UrlListProps) => {
  const { t } = useTranslation();

  const [isAddFormVisible, setIsAddFormVisible] = useState(false);

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
      {isAddFormVisible && (
        <div className="flex flex-col gap-4 shadow p-4 md:max-w-[32rem]">
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
                  onChangeNewUrlEnvironment(value as Environment)
                }
              />
            }
          />
          <FormElement
            label={`${t("details.integration_settings.url")}`}
            component={
              <Input type="text" name="url" className="md:max-w-[32rem]" />
            }
          />
          <div className="flex justify-center gap-2">
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
      )}
      {urls.map((url) => {
        return (
          <FormElement
            key={url.id}
            label={`${t(`details.integration_settings.${url.environment}`)}`}
            labelPosition={isMobile ? "top" : "left"}
            component={
              <Input
                type="text"
                name="loginProduction"
                value={url.url}
                className="md:min-w-[32rem]"
                onChange={(e) =>
                  onChangeData(
                    urls.map((url) => {
                      if (url.id === url.id) {
                        return { ...url, url: e.target.value, changed: true };
                      }
                      return url;
                    })
                  )
                }
                disabled={isDisabled}
              />
            }
          />
        );
      })}
    </>
  );
};
