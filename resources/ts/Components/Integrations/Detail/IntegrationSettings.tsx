import React, { useState } from "react";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "../../ButtonPrimary";
import { useForm } from "@inertiajs/react";
import { Integration } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { UrlList } from "./UrlList";
import { IntegrationUrl } from "../../../Pages/Integrations/Index";
import { BasicInfo } from "./BasicInfo";
import { IntegrationType } from "../../../types/IntegrationType";
import { Alert } from "../../Alert";
import { Environment } from "../../../types/Environment";

export const NEW_URL_ID_PREFIX = "new-";

const useBasicInfoForm = <T extends object>(initialFormValues: T) =>
  useForm<T>(initialFormValues);
const useUrlsForm = <T extends object>(initialFormValues: T) =>
  useForm<T>(initialFormValues);

type Props = {
  isMobile: boolean;
} & Integration;

export const IntegrationSettings = ({
  id,
  name,
  type,
  description,
  urls,
}: Props) => {
  const { t } = useTranslation();

  const [status, setStatus] = useState<"idle" | "success" | "error">("idle");

  const basicInfoForm = useBasicInfoForm({
    integrationName: name,
    description,
  });
  const urlsForm = useUrlsForm({
    urls,
  });

  urlsForm.transform((data) => ({
    ...data,
    // @ts-expect-error strip out frontend generated ids
    urls: data.urls.map((url) => {
      if (!url.id.startsWith(NEW_URL_ID_PREFIX)) {
        return url;
      }

      return {
        ...url,
        id: undefined,
      };
    }),
  }));

  const handleConfirmDeleteUrl = (toDeleteUrlId: string) => {
    urlsForm.setData((previousData) => ({
      ...previousData,
      urls: previousData.urls.filter((url) => url.id !== toDeleteUrlId),
    }));
  };

  const handleAddNewUrl = (
    type: IntegrationUrlType,
    environment: Environment
  ) => {
    urlsForm.setData((previousData) => ({
      ...previousData,
      urls: [
        ...previousData.urls,
        {
          url: "",
          id: `${NEW_URL_ID_PREFIX}${crypto.randomUUID()}`,
          type,
          environment,
        } satisfies IntegrationUrl,
      ],
    }));
  };

  const handleChangeUrlValue = (id: string, urlValue: string) => {
    urlsForm.setData((previousData) => ({
      ...previousData,
      urls: previousData.urls.map((url) => {
        if (url.id !== id) {
          return url;
        }

        return {
          ...url,
          url: urlValue,
        };
      }),
    }));
  };

  const saveBasicInfo = () => {
    return new Promise((resolve, reject) => {
      basicInfoForm.patch(`/integrations/${id}`, {
        onError: (error) => reject(error),
        onSuccess: () => resolve(undefined),
      });
    });
  };

  const saveUrls = () => {
    return new Promise((resolve, reject) => {
      urlsForm.put(`/integrations/${id}/urls`, {
        onError: (error) => reject(error),
        onSuccess: () => resolve(undefined),
      });
    });
  };

  const handleSave = async () => {
    try {
      await saveBasicInfo();
      await saveUrls();

      setStatus("success");
    } catch {
      setStatus("error");
    }
  };

  return (
    <>
      {status !== "idle" && (
        <Alert
          visible
          variant={status}
          title={t(`details.integration_settings.${status}`)}
          closable
          onClose={() => setStatus("idle")}
        />
      )}

      <BasicInfo
        name={basicInfoForm.data.integrationName}
        description={basicInfoForm.data.description}
        onChangeName={(name) => basicInfoForm.setData("integrationName", name)}
        onChangeDescription={(description) =>
          basicInfoForm.setData("description", description)
        }
        errors={basicInfoForm.errors}
      />

      {type !== IntegrationType.Widgets &&
        Object.values(IntegrationUrlType).map((type) => (
          <UrlList
            key={type}
            type={type}
            urls={urlsForm.data.urls.filter((url) => url.type === type)}
            errors={urlsForm.errors}
            onConfirmDeleteUrl={handleConfirmDeleteUrl}
            onAddNewUrl={handleAddNewUrl}
            onChangeUrlValue={handleChangeUrlValue}
          />
        ))}

      <div className="lg:grid lg:grid-cols-3 gap-6">
        <ButtonPrimary
          onClick={handleSave}
          className="col-span-2 justify-self-start"
        >
          {t("details.save")}
        </ButtonPrimary>
      </div>
    </>
  );
};
