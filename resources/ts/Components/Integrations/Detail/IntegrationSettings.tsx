import React, { useMemo } from "react";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "../../ButtonPrimary";
import { useForm } from "@inertiajs/react";
import { Integration } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { NewIntegrationUrl, UrlList } from "./UrlList";
import { IntegrationUrl } from "../../../Pages/Integrations/Index";
import { BasicInfo } from "./BasicInfo";
import { IntegrationType } from "../../../types/IntegrationType";

type Props = {
  isMobile: boolean;
  integration: Integration;
} & Integration;

export const IntegrationSettings = ({ integration, id, urls }: Props) => {
  const { t } = useTranslation();

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
    integrationName: integration.name,
    description: integration.description,
    callbackUrls,
    loginUrls,
    logoutUrls,
    newIntegrationUrls: [] as NewIntegrationUrl[],
  };

  const {
    data,
    reset,
    setData,
    patch,
    transform,
    delete: destroy,
    errors,
  } = useForm(initialFormValues);

  const handleDeleteExistingUrl = (urlId: IntegrationUrl["id"]) => {
    destroy(`/integrations/${id}/urls/${urlId}`, {
      preserveScroll: true,
      preserveState: true,
    });
  };

  const handleSave = () => {
    patch(`/integrations/${id}`, {
      preserveScroll: true,
      preserveState: true,
    });
    reset(
      "newIntegrationUrls"
    );
  };

  const handleChangeNewUrl = (newUrl: NewIntegrationUrl & { id: string }) => {
    let found = false;

    const updated = data.newIntegrationUrls.map((url) => {
      if (url.id === newUrl.id) {
        found = true;
        return newUrl;
      }

      return url;
    });

    if (!found) {
      updated.push(newUrl);
    }

    setData("newIntegrationUrls", updated);
  };

  const handleDeleteNewUrl = (fields?: string[], id?: string) => {
    const updatedUrls = data.newIntegrationUrls
      .filter((url) => fields?.includes(url.id))
      .filter((url) => url.id !== id);

    setData("newIntegrationUrls", updatedUrls);
  };

  transform((data) => ({
    ...data,
    callbackUrls: data.callbackUrls.filter((url) => url.changed),
    loginUrls: data.loginUrls.filter((url) => url.changed),
    logoutUrls: data.logoutUrls.filter((url) => url.changed),
  }));

  const hasIntegrationUrls = useMemo(
    () =>
      integration.type !== IntegrationType.Widgets &&
      integration.hasCredentials.v2,
    [integration]
  );

  return (
    <>
      <BasicInfo
        name={data.integrationName}
        description={data.description}
        onChangeName={(data) => setData("integrationName", data)}
        onChangeDescription={(data) => setData("description", data)}
        errors={errors}
      />
      <UrlList
        type={IntegrationUrlType.Login}
        urls={loginUrls}
        newIntegrationUrls={data.newIntegrationUrls}
        onDeleteExistingUrl={(urlId) => handleDeleteExistingUrl(urlId)}
        onChangeNewUrl={handleChangeNewUrl}
        onDeleteNewUrl={handleDeleteNewUrl}
        onChangeData={(data) => setData("loginUrls", data)}
        errors={errors}
        className="border-b border-b-gray-300 pb-10"
        disabled={!hasIntegrationUrls}
      />
      <UrlList
        type={IntegrationUrlType.Callback}
        urls={callbackUrls}
        newIntegrationUrls={data.newIntegrationUrls}
        onDeleteExistingUrl={(urlId) => handleDeleteExistingUrl(urlId)}
        onChangeNewUrl={handleChangeNewUrl}
        onDeleteNewUrl={handleDeleteNewUrl}
        onChangeData={(data) => {
          setData("callbackUrls", data);
        }}
        errors={errors}
        className="border-b border-b-gray-300 pb-10"
        disabled={!hasIntegrationUrls}
      />
      <UrlList
        type={IntegrationUrlType.Logout}
        urls={logoutUrls}
        newIntegrationUrls={data.newIntegrationUrls}
        onChangeData={(data) => setData("logoutUrls", data)}
        onDeleteExistingUrl={(urlId) => handleDeleteExistingUrl(urlId)}
        onChangeNewUrl={handleChangeNewUrl}
        disabled={!hasIntegrationUrls}
        onDeleteNewUrl={handleDeleteNewUrl}
        errors={errors}
      />
      <div className="lg:grid lg:grid-cols-3 gap-6">
        <div></div>
        <ButtonPrimary
          onClick={() => {
            handleSave();
          }}
          className="col-span-2 justify-self-start"
        >
          {t("details.save")}
        </ButtonPrimary>
      </div>
    </>
  );
};
