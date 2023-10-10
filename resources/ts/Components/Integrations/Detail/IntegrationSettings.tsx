import React, { useMemo } from "react";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "../../ButtonPrimary";
import { useForm } from "@inertiajs/react";
import { Integration } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { UrlList } from "./UrlList";
import { Environment } from "../../../types/Environment";
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
    newIntegrationUrl: {
      environment: Environment.Test as Environment,
      url: "",
      type: "",
    },
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

  transform((data) => ({
    ...data,
    callbackUrls: data.callbackUrls.filter((url) => url.changed),
    loginUrls: data.loginUrls.filter((url) => url.changed),
    logoutUrls: data.logoutUrls.filter((url) => url.changed),
  }));

  return (
    <div className="w-full flex flex-col max-md:px-5 px-10 py-5">
      <BasicInfo name={data.integrationName} description={data.description} />
      <UrlList
        type={IntegrationUrlType.Login}
        urls={data.loginUrls}
        newUrl={data.newIntegrationUrl}
        onDelete={(urlId) => handleDeleteUrl(urlId)}
        onChangeNewUrl={(newUrl) =>
          setData("newIntegrationUrl", {
            ...newUrl,
            type: IntegrationUrlType.Login,
          })
        }
        onChangeData={(data) => setData("loginUrls", data)}
        className="border-b border-b-gray-300"
      />
      <UrlList
        type={IntegrationUrlType.Callback}
        urls={data.callbackUrls}
        newUrl={data.newIntegrationUrl}
        onDelete={(urlId) => handleDeleteUrl(urlId)}
        onChangeNewUrl={(newUrl) =>
          setData("newIntegrationUrl", {
            ...newUrl,
            type: IntegrationUrlType.Callback,
          })
        }
        onChangeData={(data) => {
          setData("callbackUrls", data);
          console.log(data);
        }}
        className="border-b border-b-gray-300"
      />
      <UrlList
        type={IntegrationUrlType.Logout}
        urls={data.logoutUrls}
        newUrl={data.newIntegrationUrl}
        onChangeData={(data) => setData("logoutUrls", data)}
        onDelete={(urlId) => handleDeleteUrl(urlId)}
        onChangeNewUrl={(newUrl) =>
          setData("newIntegrationUrl", {
            ...newUrl,
            type: IntegrationUrlType.Logout,
          })
        }
        className="py-10"
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
    </div>
  );
};
