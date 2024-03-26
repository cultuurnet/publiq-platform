import React from "react";
import { Dialog } from "./Dialog";
import { ButtonSecondary } from "./ButtonSecondary";
import { ButtonPrimary } from "./ButtonPrimary";
import { FormElement } from "./FormElement";
import { Input } from "./Input";
import { useForm } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { IntegrationType } from "../types/IntegrationType";
import { useIsMobile } from "../hooks/useIsMobile";
import { getPriceLabelFromSubscription } from "../utils/getPriceLabelFromSubscription";
import { Subscription } from "../types/Subscription";

type Props = {
  isVisible?: boolean;
  onClose: () => void;
  title?: string;
  id: string;
  subscription: Subscription;
  type: IntegrationType;
  email: string;
};

export const ActivationDialog = ({
  isVisible,
  onClose,
  id,
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

  const priceElement = (
    <div className="flex flex-col gap-1">
      <span className="text-sm font-medium">
        {t("integrations.activation_dialog.subscription_plan")}
      </span>
      <p className="text-sm">{getPriceLabelFromSubscription(subscription)}</p>
    </div>
  );

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
        {priceElement}
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
        {priceElement}
      </>
    </Dialog>
  );
};
