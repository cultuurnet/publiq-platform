import React, { useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "../../ButtonPrimary";
import { FormDropdown } from "../../FormDropdown";
import { ButtonIcon } from "../../ButtonIcon";
import { faPencil } from "@fortawesome/free-solid-svg-icons";
import { useSectionCollapsedContext } from "../../../context/SectionCollapsedContext";
import { useForm } from "@inertiajs/react";
import { Integration } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { UrlList } from "./UrlList";

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
      <UrlList
        type={IntegrationUrlType.Login}
        urls={data.loginUrls}
        onChangeData={(data) => setData("loginUrls", data)}
        isDisabled={isDisabled}
        isMobile={isMobile}
        isAddVisible={false}
      />
      <UrlList
        type={IntegrationUrlType.Callback}
        urls={data.callbackUrls}
        onChangeData={(data) => setData("callbackUrls", data)}
        isDisabled={isDisabled}
        isMobile={isMobile}
      />
      <UrlList
        type={IntegrationUrlType.Logout}
        urls={data.logoutUrls}
        onChangeData={(data) => setData("logoutUrls", data)}
        isDisabled={isDisabled}
        isMobile={isMobile}
      />

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
