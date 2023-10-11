import React, { useState } from "react";
import { Heading } from "../../Heading";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonPrimary } from "../../ButtonPrimary";
import { FormDropdown } from "../../FormDropdown";
import { Integration } from "../../../Pages/Integrations/Index";
import { useTranslation } from "react-i18next";
import { useForm } from "@inertiajs/react";

type Props = Integration;

export const BillingInfo = ({ id, organisation, subscription }: Props) => {
  const { t } = useTranslation();
  const [isDisabled, setIsDisabled] = useState(true);

  const initialFormValues = {
    organisation,
  };

  const { data, setData, patch, errors: err } = useForm(initialFormValues);

  const errors = err as Record<string, string | undefined>;

  return (
    <FormDropdown title={t("details.billing_info.title")}>
      <div className="flex flex-col gap-5">
        <div className="flex max-sm:flex-col md:items-center gap-2">
          <Heading level={5} className="font-semibold w50">
            {t("details.billing_info.subscription")}
          </Heading>
          <p>
            {subscription.category} {"("}
            {subscription.currency === "EUR" ? "€" : subscription.currency}{" "}
            {subscription.fee / 100}
            {")"}
          </p>
        </div>

        {organisation && (
          <>
            <div className="grid md:w-[50%] ">
              <FormElement
                label={`${t("details.billing_info.name")}`}
                error={errors["organisation.name"]}
                component={
                  <Input
                    type="text"
                    name="organisation.name"
                    value={data.organisation?.name}
                    onChange={(e) =>
                      setData("organisation", {
                        // We know organisation exists
                        // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                        ...data.organisation!,
                        name: e.target.value,
                      })
                    }
                    disabled={isDisabled}
                  />
                }
              />
            </div>
            <div className="grid grid-cols-3 gap-3 max-md:grid-cols-1">
              <FormElement
                label={`${t("details.billing_info.address.street")}`}
                error={errors["organisation.address.street"]}
                component={
                  <Input
                    type="text"
                    name="organisation.address.street"
                    value={data.organisation?.address.street}
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
                    disabled={isDisabled}
                  />
                }
              />
              <FormElement
                label={`${t("details.billing_info.address.postcode")}`}
                error={errors["organisation.address.zip"]}
                component={
                  <Input
                    type="text"
                    name="organisation.address.zip"
                    value={data.organisation?.address.zip}
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
                    disabled={isDisabled}
                  />
                }
              />

              <FormElement
                label={`${t("details.billing_info.address.city")}`}
                error={errors["organisation.address.city"]}
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
                    disabled={isDisabled}
                  />
                }
              />
            </div>
            <div className="grid md:w-[50%]">
              <FormElement
                label={`${t("details.billing_info.vat")}`}
                error={errors["organisation.vat"]}
                component={
                  <Input
                    type="text"
                    name="organisation.vat"
                    value={data.organisation?.vat}
                    onChange={(e) =>
                      setData("organisation", {
                        // We know organisation exists
                        // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                        ...data.organisation!,
                        // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                        vat: e.target.value,
                      })
                    }
                    disabled={isDisabled}
                  />
                }
              />
            </div>
            {!isDisabled && (
              <div className="flex flex-col gap-2 items-center">
                <ButtonPrimary
                  onClick={() => {
                    setIsDisabled(true);

                    patch(`/integrations/${id}/billing`, {
                      preserveScroll: true,
                    });
                  }}
                >
                  {t("details.save")}
                </ButtonPrimary>
              </div>
            )}
          </>
        )}
      </div>
    </FormDropdown>
  );
};
