import React from "react";
import { Heading } from "../../Heading";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonPrimary } from "../../ButtonPrimary";
import { Integration } from "../../../Pages/Integrations/Index";
import { useTranslation } from "react-i18next";
import { useForm } from "@inertiajs/react";

type Props = Integration;

export const BillingInfo = ({ id, organisation, subscription }: Props) => {
  const { t } = useTranslation();

  const initialFormValues = {
    organisation,
  };

  const { data, setData, patch, errors: err } = useForm(initialFormValues);

  const errors = err as Record<string, string | undefined>;

  return (
    <>
      <div className="w-full max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6">
        <Heading level={3} className="font-semibold">
          {t("details.billing_info.title.subscription")}
        </Heading>
        <FormElement
          error={errors["organisation.address.street"]}
          component={
            <Input
              type="text"
              name="price"
              value={`${subscription.category} (${
                subscription.currency === "EUR" ? "€" : subscription.currency
              } ${subscription.fee / 100})`}
              className="md:min-w-[40rem]"
              disabled
            />
          }
        />
      </div>
      {data.organisation && (
        <>
          <div className="w-full max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6">
            <Heading level={3} className="font-semibold">
              {t("details.billing_info.title.organization")}
            </Heading>

            <div className="flex flex-col gap-5">
              <FormElement
                label={`${t("details.billing_info.name")}`}
                error={errors["organisation.name"]}
                className="w-full"
                component={
                  <Input
                    type="text"
                    name="organisation.name"
                    className="md:min-w-[40rem]"
                    value={data.organisation.name}
                    onChange={(e) =>
                      setData("organisation", {
                        // We know organisation exists
                        // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                        ...data.organisation!,
                        name: e.target.value,
                      })
                    }
                  />
                }
              />
              <div className="max-md:flex max-md:flex-col md:grid md:grid-cols-5 gap-3 md:min-w-[40rem]">
                <FormElement
                  label={`${t("details.billing_info.address.street")}`}
                  error={errors["organisation.address.street"]}
                  className="col-span-2"
                  component={
                    <Input
                      type="text"
                      name="organisation.address.street"
                      value={data.organisation.address.street}
                      onChange={(e) =>
                        setData("organisation", {
                          // We know organisation exists
                          // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                          ...data.organisation!,
                          address: {
                            // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                            ...data.organisation!.address,
                            street: e.target.value,
                          },
                        })
                      }
                    />
                  }
                />
                <FormElement
                  label={`${t("details.billing_info.address.postcode")}`}
                  error={errors["organisation.address.zip"]}
                  className="col-span-1"
                  component={
                    <Input
                      type="text"
                      name="organisation.address.zip"
                      value={data.organisation.address.zip}
                      onChange={(e) =>
                        setData("organisation", {
                          // We know organisation exists
                          // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                          ...data.organisation!,
                          address: {
                            // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                            ...data.organisation!.address,
                            zip: e.target.value,
                          },
                        })
                      }
                    />
                  }
                />
                <FormElement
                  label={`${t("details.billing_info.address.city")}`}
                  error={errors["organisation.address.city"]}
                  className="col-span-2"
                  component={
                    <Input
                      type="text"
                      name="organisation.address.city"
                      value={data.organisation?.address.city}
                      onChange={(e) =>
                        setData("organisation", {
                          // We know organisation exists
                          // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                          ...data.organisation!,
                          address: {
                            // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                            ...data.organisation!.address,
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
                error={errors["organisation.vat"]}
                className="w-full"
                component={
                  <Input
                    type="text"
                    name="organisation.vat"
                    value={data.organisation?.vat}
                    className="md:min-w-[40rem]"
                    onChange={(e) =>
                      setData("organisation", {
                        // We know organisation exists
                        // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                        ...data.organisation!,
                        // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                        vat: e.target.value,
                      })
                    }
                  />
                }
              />
            </div>
          </div>
          <div className="lg:grid lg:grid-cols-3 gap-6">
            <div></div>
            <ButtonPrimary
              className="col-span-2 justify-self-start"
              onClick={() => {
                patch(`/integrations/${id}/billing`, {
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
