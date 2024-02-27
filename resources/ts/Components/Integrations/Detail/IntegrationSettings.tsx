import React, { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "../../ButtonPrimary";
import { useForm } from "@inertiajs/react";
import { Integration } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { NewIntegrationUrl, UrlList } from "./UrlList";
import { IntegrationUrl } from "../../../Pages/Integrations/Index";
import { BasicInfo } from "./BasicInfo";
import { IntegrationType } from "../../../types/IntegrationType";
import { Alert } from "../../Alert";

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
    processing,
    hasErrors,
    wasSuccessful,
  } = useForm(initialFormValues);

  const [isDeleteFunction, setIsDeleteFunction] = useState(false);
  const [isErrorMessageVisible, setIsErrorMessageVisible] = useState(false);
  const [isSuccessMessageVisible, setIsSuccessMessageVisible] = useState(false);

  const handleDeleteExistingUrl = (urlId: IntegrationUrl["id"]) => {
    destroy(`/integrations/${id}/urls/${urlId}`, {
      preserveScroll: true,
      preserveState: true,
    });
    setIsDeleteFunction(true);
  };

  const handleSave = () => {
    patch(`/integrations/${id}/urls`, {
      preserveScroll: true,
      preserveState: true,
    });
    setIsDeleteFunction(false);
  };

  useEffect(() => {
    if (!processing && !isDeleteFunction && !hasErrors) {
      reset("newIntegrationUrls");
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isDeleteFunction, hasErrors, processing]);

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
    const updatedUrls = data.newIntegrationUrls.filter(
      (url) => fields?.includes(url.id) && url.id !== id
    );

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

  useEffect(() => {
    if (!processing && hasErrors) {
      {
        window.scrollTo({
          top: 0,
          left: 0,
          behavior: "smooth",
        });

        setIsErrorMessageVisible(true);
        setIsSuccessMessageVisible(false);
      }
    }
  }, [processing, hasErrors]);

  useEffect(() => {
    if (!processing && !hasErrors && wasSuccessful && !isDeleteFunction) {
      window.scrollTo({
        top: 0,
        left: 0,
        behavior: "smooth",
      });

      setIsSuccessMessageVisible(true);
      setIsErrorMessageVisible(false);
    }
  }, [processing, hasErrors, wasSuccessful, isDeleteFunction]);

  return (
    <>
      <Alert
        variant="error"
        visible={isErrorMessageVisible}
        title={t("details.integration_settings.error")}
      />
      <Alert
        variant="success"
        visible={isSuccessMessageVisible}
        title={t("details.integration_settings.success")}
      />
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
        urlsFormValues={data.loginUrls}
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
        urlsFormValues={data.callbackUrls}
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
        urlsFormValues={data.logoutUrls}
        newIntegrationUrls={data.newIntegrationUrls}
        onChangeData={(data) => setData("logoutUrls", data)}
        onDeleteExistingUrl={(urlId) => handleDeleteExistingUrl(urlId)}
        onChangeNewUrl={handleChangeNewUrl}
        disabled={!hasIntegrationUrls}
        onDeleteNewUrl={handleDeleteNewUrl}
        errors={errors}
      />
      <div className="lg:grid lg:grid-cols-3 gap-6">
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
