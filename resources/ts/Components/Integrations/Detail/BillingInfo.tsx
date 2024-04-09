import React, { useContext } from "react";
import { Heading } from "../../Heading";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonPrimary } from "../../ButtonPrimary";
import { useTranslation } from "react-i18next";
import { useForm } from "@inertiajs/react";
import { Alert } from "../../Alert";
import { IntegrationType } from "../../../types/IntegrationType";
import { IntegrationStatus } from "../../../types/IntegrationStatus";
import { PricingPlanContext } from "../../../Context/PricingPlan";
import { formatCurrency } from "../../../utils/formatCurrency";
import { formatPricing } from "../../../utils/formatPricing";
import type { Integration } from "../../../types/Integration";

type Props = Integration;

export const BillingInfo = ({
  id,
  organization,
  subscription,
  coupon,
  status,
}: Props) => {
  const { t } = useTranslation();
  const initialFormValues = {
    organization,
  };

  const { data, setData, patch, errors: err } = useForm(initialFormValues);

  const errors = err as Record<string, string | undefined>;

  const pricingPlan = useContext(PricingPlanContext);

  return (
    <>
      <div className="w-full max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6">
        <Heading level={4} className="font-semibold">
          {t("details.billing_info.title.subscription")}
        </Heading>
        <FormElement
          component={
            <Input
              type="text"
              name="price"
              value={`${pricingPlan.title} (${pricingPlan.price})`}
              className="md:min-w-[40rem]"
              disabled
            />
          }
        />
        {coupon?.isDistributed && (
          <Alert
            className={"col-span-2 col-start-2"}
            variant="success"
            title={t("details.billing_info.coupon_used", {
              price: formatCurrency(subscription.currency, coupon.reduction),
            })}
          />
        )}
      </div>
      {status !== IntegrationStatus.Active &&
        subscription.integrationType !== IntegrationType.EntryApi && (
          <div className={"grid grid-cols-3 gap-10"}>
            <Alert
              className={"col-span-2 col-start-2"}
              variant="info"
              title={t("details.billing_info.free_until_live")}
            />
          </div>
        )}
      {status === IntegrationStatus.Active && (
        <div className="w-full max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6">
          <Heading level={4} className="font-semibold">
            {t("details.billing_info.to_pay")}
          </Heading>
          <div className="w-full block relative md:min-w-[40rem]">
            {t(`pricing_plan.basic.price`, {
              price: formatPricing({
                currency: subscription.currency,
                price: Math.max(
                  subscription.fee / 100 - (coupon?.reduction ?? 0),
                  0
                ),
              }),
            })}
          </div>
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
