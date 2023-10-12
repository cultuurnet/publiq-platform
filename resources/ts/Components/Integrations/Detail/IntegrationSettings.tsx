import React, { useMemo } from "react";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "../../ButtonPrimary";
import { useForm } from "@inertiajs/react";
import { Integration } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { NewIntegrationUrl, UrlList } from "./UrlList";
import { IntegrationUrl } from "../../../Pages/Integrations/Index";
import { BasicInfo } from "./BasicInfo";

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
    setData,
    patch,
    transform,
    delete: destroy,
  } = useForm(initialFormValues);

  const handleDeleteUrl = (urlId: IntegrationUrl["id"]) => {
    destroy(`/integrations/${id}/urls/${urlId}`, {
      preserveScroll: true,
      preserveState: false,
    });
  };

  const handleSave = () =>
    patch(`/integrations/${id}`, {
      preserveScroll: true,
      preserveState: false,
    });

  const handleChangeNewUrl = (newUrl: NewIntegrationUrl) => {
    let found = false;

    const updated = data.newIntegrationUrls.map((url) => {
      if (url.type === newUrl.type && url.environment === newUrl.environment) {
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

  transform((data) => ({
    ...data,
    callbackUrls: data.callbackUrls.filter((url) => url.changed),
    loginUrls: data.loginUrls.filter((url) => url.changed),
    logoutUrls: data.logoutUrls.filter((url) => url.changed),
  }));

  return (
    <>
      <BasicInfo
        name={data.integrationName}
        description={data.description}
        onChangeName={(data) => setData("integrationName", data)}
        onChangeDescription={(data) => setData("description", data)}
      />
      <UrlList
        type={IntegrationUrlType.Login}
        urls={data.loginUrls}
        newUrls={data.newIntegrationUrls}
        onDelete={(urlId) => handleDeleteUrl(urlId)}
        onChangeNewUrl={handleChangeNewUrl}
        onChangeData={(data) => setData("loginUrls", data)}
        className="border-b border-b-gray-300 pb-10"
      />
      <UrlList
        type={IntegrationUrlType.Callback}
        urls={data.callbackUrls}
        newUrls={data.newIntegrationUrls}
        onDelete={(urlId) => handleDeleteUrl(urlId)}
        onChangeNewUrl={handleChangeNewUrl}
        onChangeData={(data) => {
          setData("callbackUrls", data);
        }}
        className="border-b border-b-gray-300 pb-10"
      />
      <UrlList
        type={IntegrationUrlType.Logout}
        urls={data.logoutUrls}
        newUrls={data.newIntegrationUrls}
        onChangeData={(data) => setData("logoutUrls", data)}
        onDelete={(urlId) => handleDeleteUrl(urlId)}
        onChangeNewUrl={handleChangeNewUrl}
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
