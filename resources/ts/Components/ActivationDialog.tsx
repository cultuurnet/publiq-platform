import React, { useContext, useEffect, useState } from "react";
import { Dialog } from "./Dialog";
import { ButtonSecondary } from "./ButtonSecondary";
import { ButtonPrimary } from "./ButtonPrimary";
import { FormElement } from "./FormElement";
import { Input } from "./Input";
import { router, useForm } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { IntegrationType } from "../types/IntegrationType";
import { useIsMobile } from "../hooks/useIsMobile";
import type { Subscription } from "../types/Subscription";
import type { PricingPlan } from "../hooks/useGetPricingPlans";
import { formatCurrency } from "../utils/formatCurrency";
import { Heading } from "./Heading";
import { CouponInfoContext } from "../Context/CouponInfo";
import { faTrash } from "@fortawesome/free-solid-svg-icons";
import { ButtonIcon } from "./ButtonIcon";
import { debounce } from "lodash";

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

        {couponInfo && (
          <>
            <span className="text-publiq-orange text-right">-</span>
            <span className="text-publiq-orange">
              {`${formatCurrency(
                subscription.currency,
                couponInfo.reduction
              )} / ${t("integrations.activation_dialog.price_overview.year")} (${t("integrations.activation_dialog.price_overview.coupon")})`}
            </span>

            <span className="col-start-2">
              {`${formatCurrency(
                subscription.currency,
                subscription.price - couponInfo.reduction
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

type Address = {
  street: string;
  zip: string;
  city: string;
  country: string;
};

type Organization = {
  name: string;
  invoiceEmail: string;
  vat: string;
  address: Address;
};

type InitialValues = {
  organization: Organization;
  organizers: Organizer[];
  coupon: string;
};

type Organizer = {
  name: string;
  id: string;
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

  const initialValuesOrganization: InitialValues = {
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
    organizers: [],
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

  const isBillingInfoAndPriceOverviewVisible =
    type !== IntegrationType.EntryApi && type !== IntegrationType.UiTPAS;

  const [isSearchListVisible, setIsSearchListVisible] = useState(false);

  const [organizerList, setOrganizerList] = useState<Organizer[]>([]);

  const handleGetOrganizers = debounce(
    (e: React.ChangeEvent<HTMLInputElement>) => {
      setOrganizerList([]);
      router.post(
        `/integrations/${id}/organizers`,
        {
          organizer: e.target.value,
        },
        {
          preserveScroll: true,
          preserveState: true,
          onSuccess: (page) => {
            if (Array.isArray(page.props.organizers)) {
              const organizers = page.props.organizers.map((organizer) => {
                if (
                  typeof organizer.name === "object" &&
                  "nl" in organizer.name
                ) {
                  return { name: organizer.name.nl, id: organizer.id };
                }
                return organizer;
              });
              setOrganizerList(organizers);
            }
          },
          onError: (errors) => {
            console.error(errors);
          },
        }
      );
    },
    250
  );

  const handleAddOrganizers = (organizer: Organizer) => {
    let updatedOrganizers = [...organizationForm.data.organizers, organizer];
    updatedOrganizers = [...new Set(updatedOrganizers)];
    organizationForm.setData("organizers", updatedOrganizers);
    setIsSearchListVisible(false);
  };

  const handleDeleteOrganizer = (deletedOrganizer: string) => {
    const updatedOrganizers = organizationForm.data.organizers.filter(
      (organizer) => organizer.name !== deletedOrganizer
    );
    organizationForm.setData("organizers", updatedOrganizers);
  };

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
        {type === IntegrationType.UiTPAS && (
          <Heading level={5} className="font-semibold">
            {t("integrations.activation_dialog.uitpas.partner")}
          </Heading>
        )}
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
        {type === IntegrationType.UiTPAS && (
          <>
            <div className="flex flex-col gap-1">
              <Heading level={5} className="font-semibold">
                {t("integrations.activation_dialog.uitpas.organizers.title")}
              </Heading>
              <Heading level={5}>
                {t("integrations.activation_dialog.uitpas.organizers.info")}
              </Heading>
            </div>
            <div className="flex gap-2 flex-wrap">
              {organizationForm.data.organizers.length > 0 &&
                organizationForm.data.organizers.map((organizer, index) => (
                  <div
                    key={`${organizer}${index}`}
                    className="border rounded px-2 py-1 flex gap-1"
                  >
                    <p>{organizer.name}</p>
                    <ButtonIcon
                      icon={faTrash}
                      size="sm"
                      className="text-icon-gray"
                      onClick={() => handleDeleteOrganizer(organizer.name)}
                    />
                  </div>
                ))}
            </div>
            <FormElement
              label={t(
                "integrations.activation_dialog.uitpas.organizers.label"
              )}
              required
              error={organizationFormErrors["organizers"]}
              className="w-full"
              component={
                <>
                  <Input
                    type="text"
                    name="organizers"
                    onChange={(e) => {
                      e.target.value !== ""
                        ? (setIsSearchListVisible(true), handleGetOrganizers(e))
                        : setIsSearchListVisible(false);
                    }}
                    onBlur={(e) => {
                      e.target.value = "";
                      const timeoutId = setTimeout(() => {
                        setIsSearchListVisible(false);
                        clearTimeout(timeoutId);
                      }, 200);
                    }}
                  />
                  {organizerList &&
                    organizerList.length > 0 &&
                    isSearchListVisible && (
                      <ul className="border rounded">
                        {organizerList.map((organizer) => (
                          <li
                            key={`${organizer.id}`}
                            onClick={() => handleAddOrganizers(organizer)}
                            className="border-b px-3 py-1 hover:bg-gray-100"
                          >
                            {organizer.name}
                          </li>
                        ))}
                      </ul>
                    )}
                </>
              }
            />
          </>
        )}
        {isBillingInfoAndPriceOverviewVisible && (
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
        {isBillingInfoAndPriceOverviewVisible && (
          <PriceOverview
            subscription={subscription}
            pricingPlan={pricingPlan}
            coupon={organizationForm.data.coupon}
          />
        )}
      </>
    </Dialog>
  );
};
