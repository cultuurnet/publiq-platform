import React, { useMemo, useState } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonPrimary } from "../../ButtonPrimary";
import { FormDropdown } from "../../FormDropdown";
import { ButtonIcon } from "../../ButtonIcon";
import { faPencil } from "@fortawesome/free-solid-svg-icons";
import { useSectionCollapsedContext } from "../../../context/SectionCollapsedContext";
import { useForm } from "@inertiajs/react";
import { Integration } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";

type Props = {
  isMobile: boolean;
} & Integration;

export const IntegrationSettings = ({ isMobile, id, urls }: Props) => {
  const { t } = useTranslation();

  const [isDisabled, setIsDisabled] = useState(true);

  const [collapsed, setCollapsed] = useSectionCollapsedContext();

  const callbackUrls = useMemo(
    () =>
      urls
        .filter((url) => url.type === IntegrationUrlType.Callback)
        .map((url) => ({ ...url, changed: false })),
    [urls]
  );

  const loginUrls = useMemo(
    () =>
      urls
        .filter((url) => url.type === IntegrationUrlType.Login)
        .map((url) => ({ ...url, changed: false })),
    [urls]
  );

  const logoutUrls = useMemo(
    () =>
      urls
        .filter((url) => url.type === IntegrationUrlType.Logout)
        .map((url) => ({ ...url, changed: false })),
    [urls]
  );

  const initialFormValues = {
    callbackUrls,
    loginUrls,
    logoutUrls,
  };

  const { data, setData, patch, transform } = useForm(initialFormValues);

  transform((data) => ({
    callbackUrls: data.callbackUrls.filter((url) => url.changed),
    loginUrls: data.loginUrls.filter((url) => url.changed),
    logoutUrls: data.logoutUrls.filter((url) => url.changed),
  }));

  return (
    <FormDropdown
      title={t("details.integration_settings.title")}
      actions={
        <ButtonIcon
          icon={faPencil}
          className="text-icon-gray"
          onClick={() => setIsDisabled((prev) => !prev)}
        />
      }
      isCollapsed={collapsed.integrationsSettings}
      onChangeCollapsed={(newValue) =>
        setCollapsed((prev) => ({ ...prev, integrationsSettings: newValue }))
      }
    >
      <Heading className="font-semibold" level={3}>
        {t("details.integration_settings.login")}
      </Heading>
      {data.loginUrls.map((loginUrl) => {
        return (
          <FormElement
            key={loginUrl.id}
            label={`${t(
              `details.integration_settings.${loginUrl.environment}`
            )}`}
            labelPosition={isMobile ? "top" : "left"}
            component={
              <Input
                type="text"
                name="loginProduction"
                value={loginUrl.url}
                className="md:min-w-[32rem]"
                onChange={(e) =>
                  setData(
                    "loginUrls",
                    data.loginUrls.map((url) => {
                      if (url.id === loginUrl.id) {
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
      {/* 
      

      <FormElement
        label={`${t("details.integration_settings.production")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="loginProduction"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      />
      <Heading className="font-semibold" level={3}>
        {t("details.integration_settings.callback")}
      </Heading>
      <FormElement
        label={`${t("details.integration_settings.test")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="callbackTest"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      />
      <FormElement
        label={`${t("details.integration_settings.production")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="callbackProduction"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      />
      <Heading className="font-semibold" level={3}>
        {t("details.integration_settings.logout")}
      </Heading>
      <FormElement
        label={`${t("details.integration_settings.test")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="logoutTest"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      />
      <FormElement
        label={`${t("details.integration_settings.production")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="logoutProduction"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      /> */}
      {!isDisabled && (
        <div className="flex flex-col items-start md:pl-[10.5rem]">
          <ButtonPrimary
            onClick={() => {
              setIsDisabled(true);
              patch(`/integrations/${id}`);
            }}
          >
            {t("details.save")}
          </ButtonPrimary>
        </div>
      )}
    </FormDropdown>
  );
};
