import React, { useCallback, useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "../../ButtonPrimary";
import { useForm, usePage } from "@inertiajs/react";
import { Integration } from "../../../Pages/Integrations/Index";
import { IntegrationUrlType } from "../../../types/IntegrationUrlType";
import { UrlList } from "./UrlList";
import { IntegrationUrl } from "../../../Pages/Integrations/Index";
import { BasicInfo } from "./BasicInfo";
import { IntegrationType } from "../../../types/IntegrationType";
import { Alert } from "../../Alert";
import { Environment } from "../../../types/Environment";

export const NEW_URL_ID_PREFIX = "new-";

export const createEmptyIntegrationUrl = (
  type: IntegrationUrlType,
  environment: Environment
): IntegrationUrl => ({
  id: `${NEW_URL_ID_PREFIX}${crypto.randomUUID()}`,
  url: "",
  type,
  environment,
});

const useBasicInfoForm = <T extends object>(initialFormValues: T) => {
  const form = useForm<T>(initialFormValues);
  const page = usePage();

  const save = useCallback(
    () =>
      new Promise((resolve, reject) => {
        form.patch(`/integrations/${page.props.id}`, {
          onError: (error) => reject(error),
          onSuccess: () => resolve(undefined),
          only: ["name", "description", "errors"],
        });
      }),
    // form is not a stable reference and triggers whenever a field value changes
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [page.props.id]
  );

  return { ...form, save };
};
const useUrlsForm = (initialFormValues: { urls: IntegrationUrl[] }) => {
  const urlsWithDefaultEmptyValues = useMemo(() => {
    // foreach type and environment
    // there should be at least 1 field
    const hasValues: Record<
      IntegrationUrlType,
      Record<Environment, boolean>
    > = {
      [IntegrationUrlType.Login]: {
        [Environment.Test]: false,
        [Environment.Prod]: false,
      },
      [IntegrationUrlType.Logout]: {
        [Environment.Test]: false,
        [Environment.Prod]: false,
      },
      [IntegrationUrlType.Callback]: {
        [Environment.Test]: false,
        [Environment.Prod]: false,
      },
    };

    // find the missing values
    initialFormValues.urls.forEach((url) => {
      hasValues[url.type][url.environment] = !!url.url;
    });

    const withEmptyValues: IntegrationUrl[] = [...initialFormValues.urls];

    (Object.keys(hasValues) as IntegrationUrlType[]).forEach((type) => {
      (Object.keys(hasValues[type]) as Environment[]).forEach((environment) => {
        if (!hasValues[type][environment as Environment]) {
          withEmptyValues.push(createEmptyIntegrationUrl(type, environment));
        }
      });
    });

    return withEmptyValues;
  }, [initialFormValues.urls]);

  const form = useForm({
    ...initialFormValues,
    urls: urlsWithDefaultEmptyValues,
  });

  useEffect(() => {
    if (form.hasErrors) return;
    // if (Object.keys(page.props.errors).length > 0) return;
    form.setData((previousData) => ({
      ...previousData,
      urls: urlsWithDefaultEmptyValues,
    }));
    // form is not a stable reference and triggers whenever a field value changes
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [form.hasErrors, urlsWithDefaultEmptyValues]);

  const page = usePage();

  const save = useCallback(
    () =>
      new Promise((resolve, reject) => {
        form.put(`/integrations/${page.props.id}/urls`, {
          onError: (error) => reject(error),
          onSuccess: () => resolve(undefined),
          only: ["urls", "errors"],
        });
      }),
    // form is not a stable reference and triggers whenever a field value changes
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [page.props.id]
  );

  return { ...form, save };
};

type Props = {
  isMobile: boolean;
} & Integration;

export const IntegrationSettings = ({
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
    urls: data.urls
      .map((url) => {
        if (!url.id.startsWith(NEW_URL_ID_PREFIX)) {
          return url;
        }

        return {
          ...url,
          id: undefined,
        };
      })
      .filter((url) => !(typeof url.id === "undefined" && url.url === "")),
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
        createEmptyIntegrationUrl(type, environment),
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

  const handleSave = async () => {
    setStatus("idle");

    try {
      if (basicInfoForm.isDirty) {
        await basicInfoForm.save();
      }
      if (urlsForm.isDirty) {
        await urlsForm.save();
      }

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
