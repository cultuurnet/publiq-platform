import React, { useContext } from "react";
import { Dialog } from "./Dialog";
import { ButtonSecondary } from "./ButtonSecondary";
import { ButtonPrimary } from "./ButtonPrimary";
import { FormElement } from "./FormElement";
import { Input } from "./Input";
import { useForm } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import type { IntegrationType } from "../types/IntegrationType";
import { useIsMobile } from "../hooks/useIsMobile";
import type { Subscription } from "../types/Subscription";
import type { PricingPlan } from "../hooks/useGetPricingPlans";
import { formatCurrency } from "../utils/formatCurrency";
import { Heading } from "./Heading";
import { CouponInfoContext } from "../Context/CouponInfo";

const PriceOverview = ({
  coupon,
  subscription,
  pricingPlan,
}: {
  coupon?: string;
  subscription: Subscription;
  pricingPlan: PricingPlan;
}) => {
  const { t } = useTranslation();

  const couponInfo = useContext(CouponInfoContext);

  return (
    <section className="flex flex-col flex-1 gap-2 border-t mt-4 pt-4 text-sm">
      <Heading level={4}>
        {t("integrations.activation_dialog.price_overview.title")}
      </Heading>

      <div className="grid grid-cols-2 gap-1">
        <span className="col-span-2">
          {t("integrations.activation_dialog.price_overview.subscription_plan")}{" "}
          {pricingPlan.title}
        </span>

        <span className="pl-4">{`${t("integrations.activation_dialog.price_overview.setup")}`}</span>
        <span>{formatCurrency(subscription.currency, subscription.fee)}</span>

        <span className="pl-4">{`${t("integrations.activation_dialog.price_overview.plan")}`}</span>
        <span
          className={coupon ? "line-through" : ""}
        >{`${formatCurrency(subscription.currency, subscription.price)} / ${t("integrations.activation_dialog.price_overview.year")}`}</span>

        {coupon && (
          <>
            <span className="text-publiq-orange text-right">-</span>
            <span className="text-publiq-orange">
              {`${formatCurrency(
                subscription.currency,
                couponInfo.reductionAmount
              )} / ${t("integrations.activation_dialog.price_overview.year")} (${t("integrations.activation_dialog.price_overview.coupon")})`}
            </span>

            <span className="col-start-2">
              {`${formatCurrency(
                subscription.currency,
                subscription.price - couponInfo.reductionAmount
              )} / ${t("integrations.activation_dialog.price_overview.year")}`}
            </span>
          </>
        )}
      </div>
    </section>
  );
};

type Props = {
  isVisible?: boolean;
  onClose: () => void;
  title?: string;
  id: string;
  subscription: Subscription;
  pricingPlan: PricingPlan;
  type: IntegrationType;
  email: string;
};

export const ActivationDialog = ({
  isVisible,
  onClose,
  id,
  pricingPlan,
  subscription,
  type,
}: Props) => {
  const { t } = useTranslation();

  const isMobile = useIsMobile();

  const initialValuesOrganization = {
    organization: {
      name: "",
      invoiceEmail: "",
      vat: "",
      address: {
        street: "",
        zip: "",
        city: "",
        country: "Belgium",
      },
    },
    coupon: "",
  };

  const organizationForm = useForm(initialValuesOrganization);

  const handleSubmit = () => {
    organizationForm.post(`/integrations/${id}/activation`, {
      onSuccess: () => onClose(),
    });
  };

  const organizationFormErrors = organizationForm.errors as Record<
    string,
    string | undefined
  >;

  if (!isVisible) {
    return null;
  }

  return (
    <Dialog
      title={t("integrations.activation_dialog.title")}
      actions={
        <>
          <ButtonSecondary onClick={onClose}>
            {t("dialog.cancel")}
          </ButtonSecondary>
          <ButtonPrimary onClick={handleSubmit}>
            {t("dialog.confirm")}
          </ButtonPrimary>
        </>
      }
      isVisible
      onClose={onClose}
      isFullscreen={isMobile}
      contentStyles="gap-3"
    >
      <>
        <FormElement
          label={`${t("details.billing_info.name")}`}
          required
          error={organizationFormErrors["organization.name"]}
          className="w-full"
          component={
            <Input
              type="text"
              name="organization.name"
              onChange={(e) => {
                if (!organizationForm.data.organization) return;

                organizationForm.setData("organization", {
                  ...organizationForm.data.organization,
                  name: e.target.value,
                });
              }}
            />
          }
        />
        <div className="max-md:flex max-md:flex-col md:grid md:grid-cols-5 gap-3">
          <FormElement
            label={`${t("details.billing_info.address.street")}`}
            required
            error={organizationFormErrors["organization.address.street"]}
            className="col-span-2"
            component={
              <Input
                type="text"
                name="organization.address.street"
                onChange={(e) => {
                  if (!organizationForm.data.organization) return;

                  organizationForm.setData("organization", {
                    ...organizationForm.data.organization,
                    address: {
                      ...organizationForm.data.organization.address,
                      street: e.target.value,
                    },
                  });
                }}
              />
            }
          />
          <FormElement
            label={`${t("details.billing_info.address.postcode")}`}
            required
            error={organizationFormErrors["organization.address.zip"]}
            className="col-span-1"
            component={
              <Input
                type="text"
                name="organization.address.zip"
                onChange={(e) => {
                  if (!organizationForm.data.organization) return;

                  organizationForm.setData("organization", {
                    ...organizationForm.data.organization,
                    address: {
                      ...organizationForm.data.organization.address,
                      zip: e.target.value,
                    },
                  });
                }}
              />
            }
          />
          <FormElement
            label={`${t("details.billing_info.address.city")}`}
            required
            error={organizationFormErrors["organization.address.city"]}
            className="col-span-2"
            component={
              <Input
                type="text"
                name="organization.address.city"
                onChange={(e) => {
                  if (!organizationForm.data.organization) return;

                  organizationForm.setData("organization", {
                    ...organizationForm.data.organization,
                    address: {
                      ...organizationForm.data.organization.address,
                      city: e.target.value,
                    },
                  });
                }}
              />
            }
          />
        </div>
        {type !== "entry-api" && (
          <>
            <FormElement
              label={`${t("details.billing_info.vat")}`}
              required
              error={organizationFormErrors["organization.vat"]}
              className="w-full"
              component={
                <Input
                  type="text"
                  name="organization.vat"
                  onChange={(e) => {
                    if (!organizationForm.data.organization) return;

                    organizationForm.setData("organization", {
                      ...organizationForm.data.organization,
                      vat: e.target.value,
                    });
                  }}
                />
              }
            />
            <FormElement
              label={`${t("integrations.activation_dialog.contact")}`}
              required
              error={organizationFormErrors["organization.invoiceEmail"]}
              info={`${t("integrations.activation_dialog.contact_description")}`}
              component={
                <Input
                  type="text"
                  name="invoiceEmail"
                  onChange={(e) => {
                    if (!organizationForm.data.organization) return;

                    organizationForm.setData("organization", {
                      ...organizationForm.data.organization,
                      invoiceEmail: e.target.value,
                    });
                  }}
                />
              }
            />
            <FormElement
              label={`${t("integrations.activation_dialog.coupon")}`}
              error={organizationForm.errors.coupon}
              className="col-span-2"
              component={
                <Input
                  type="text"
                  name="coupon"
                  value={organizationForm.data.coupon}
                  onChange={(e) =>
                    organizationForm.setData("coupon", e.target.value)
                  }
                />
              }
            />
          </>
        )}
        <PriceOverview
          subscription={subscription}
          pricingPlan={pricingPlan}
          coupon={organizationForm.data.coupon}
        />
      </>
    </Dialog>
  );
};
