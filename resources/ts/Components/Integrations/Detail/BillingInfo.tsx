import React from "react";
import { Heading } from "../../Heading";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonPrimary } from "../../ButtonPrimary";
import { Integration } from "../../../Pages/Integrations/Index";
import { useTranslation } from "react-i18next";
import { useForm } from "@inertiajs/react";
import { Alert } from "../../Alert";
import { IntegrationType } from "../../../types/IntegrationType";
import { IntegrationStatus } from "../../../types/IntegrationStatus";
import { getPriceLabelFromSubscription } from "../../../utils/getPriceLabelFromSubscription";

type Props = Integration;

export const BillingInfo = ({
  id,
  organization,
  subscription,
  status,
}: Props) => {
  const { t } = useTranslation();
  const initialFormValues = {
    organization,
  };

  const { data, setData, patch, errors: err } = useForm(initialFormValues);

  const errors = err as Record<string, string | undefined>;

  return (
    <>
      <div className="w-full max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6">
        <Heading level={4} className="font-semibold">
          {t("details.billing_info.title.subscription")}
        </Heading>
        <FormElement
          error={errors["organization.address.street"]}
          component={
            <Input
              type="text"
              name="price"
              value={getPriceLabelFromSubscription(subscription)}
              className="md:min-w-[40rem]"
              disabled
            />
          }
        />
      </div>
      {subscription.integrationType !== IntegrationType.EntryApi &&
        status !== IntegrationStatus.Active && (
          <div className={"grid grid-cols-3 gap-10"}>
            <Alert
              className={"col-span-2 col-start-2"}
              variant="info"
              title={t("details.billing_info.free_until_live")}
            />
          </div>
        )}
      {data.organization && (
        <>
          <div className="w-full max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6">
            <Heading level={4} className="font-semibold">
              {t("details.billing_info.title.organization")}
            </Heading>

            <div className="flex flex-col gap-5">
              <FormElement
                label={`${t("details.billing_info.name")}`}
                error={errors["organization.name"]}
                className="w-full"
                component={
                  <Input
                    type="text"
                    name="organization.name"
                    className="md:min-w-[40rem]"
                    value={data.organization.name}
                    onChange={(e) =>
                      setData("organization", {
                        // We know organization exists
                        ...data.organization!,
                        name: e.target.value,
                      })
                    }
                  />
                }
              />
              <div className="max-md:flex max-md:flex-col md:grid md:grid-cols-5 gap-3 md:min-w-[40rem]">
                <FormElement
                  label={`${t("details.billing_info.address.street")}`}
                  error={errors["organization.address.street"]}
                  className="col-span-2"
                  component={
                    <Input
                      type="text"
                      name="organization.address.street"
                      value={data.organization.address.street}
                      onChange={(e) =>
                        setData("organization", {
                          // We know organization exists
                          ...data.organization!,
                          address: {
                            ...data.organization!.address,
                            street: e.target.value,
                          },
                        })
                      }
                    />
                  }
                />
                <FormElement
                  label={`${t("details.billing_info.address.postcode")}`}
                  error={errors["organization.address.zip"]}
                  className="col-span-1"
                  component={
                    <Input
                      type="text"
                      name="organization.address.zip"
                      value={data.organization.address.zip}
                      onChange={(e) =>
                        setData("organization", {
                          // We know organization exists
                          ...data.organization!,
                          address: {
                            ...data.organization!.address,
                            zip: e.target.value,
                          },
                        })
                      }
                    />
                  }
                />
                <FormElement
                  label={`${t("details.billing_info.address.city")}`}
                  error={errors["organization.address.city"]}
                  className="col-span-2"
                  component={
                    <Input
                      type="text"
                      name="organization.address.city"
                      value={data.organization?.address.city}
                      onChange={(e) =>
                        setData("organization", {
                          // We know organization exists
                          ...data.organization!,
                          address: {
                            ...data.organization!.address,
                            city: e.target.value,
                          },
                        })
                      }
                    />
                  }
                />
              </div>
              <FormElement
                label={`${t("details.billing_info.vat")}`}
                error={errors["organization.vat"]}
                className="w-full"
                component={
                  <Input
                    type="text"
                    name="organization.vat"
                    value={data.organization?.vat}
                    className="md:min-w-[40rem]"
                    onChange={(e) =>
                      setData("organization", {
                        // We know organization exists
                        ...data.organization!,
                        vat: e.target.value,
                      })
                    }
                  />
                }
              />
            </div>
          </div>
          <div className="lg:grid lg:grid-cols-3 gap-6">
            <ButtonPrimary
              className="col-span-2 justify-self-start"
              onClick={() => {
                patch(`/integrations/${id}/organization`, {
                  preserveScroll: true,
                });
              }}
            >
              {t("details.save")}
            </ButtonPrimary>
          </div>
        </>
      )}
    </>
  );
};
