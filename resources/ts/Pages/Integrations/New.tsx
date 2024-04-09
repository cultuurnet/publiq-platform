import type { FormEvent, ReactNode } from "react";
import React from "react";
import { useState } from "react";
import { router, useForm } from "@inertiajs/react";
import Layout from "../../layouts/Layout";
import { Heading } from "../../Components/Heading";
import { FormElement } from "../../Components/FormElement";
import { Input } from "../../Components/Input";
import { Trans, useTranslation } from "react-i18next";
import { Card } from "../../Components/Card";
import { ButtonPrimary } from "../../Components/ButtonPrimary";
import { Page } from "../../Components/Page";
import { Link } from "../../Components/Link";
import { useIntegrationTypesInfo } from "../../Components/IntegrationTypes";
import {
  IntegrationType,
  isIntegrationType,
} from "../../types/IntegrationType";
import {
  RadioButtonGroup,
  RichRadioButton,
} from "../../Components/RadioButtonGroup";
import type { Subscription } from "../../types/Subscription";
import { useGetPricingPlans } from "../../hooks/useGetPricingPlans";
import type { SubscriptionCategory } from "../../types/SubscriptionCategory";

type Props = {
  subscriptions: Subscription[];
};

const New = ({ subscriptions }: Props) => {
  const { t } = useTranslation();
  const { i18n } = useTranslation();

  const freeSubscriptionId = subscriptions.find(
    (subscription) =>
      subscription.integrationType === IntegrationType.EntryApi &&
      subscription.price === 0
  )?.id;

  const basicSubscriptionIds = subscriptions
    .filter((subscription) =>
      subscription.category.includes(SubscriptionCategory.Basic)
    )
    .map((subscription) => subscription.id);

  const url = new URL(document.location.href);
  const activeTypeFromUrl = url.searchParams.get("type");
  const activeType = isIntegrationType(activeTypeFromUrl)
    ? activeTypeFromUrl
    : IntegrationType.EntryApi;

  const initialFormValues = {
    integrationType: activeType,
    subscriptionId:
      activeType === IntegrationType.EntryApi && !!freeSubscriptionId
        ? freeSubscriptionId
        : "",
    integrationName: "",
    description: "",
    organizationFunctionalContact: "",
    firstNameFunctionalContact: "",
    lastNameFunctionalContact: "",
    emailFunctionalContact: "",
    organizationTechnicalContact: "",
    firstNameTechnicalContact: "",
    lastNameTechnicalContact: "",
    emailTechnicalContact: "",
    agreement: "",
    coupon: "",
  };

  const [hasCoupon, setHasCoupon] = useState(false);

  const { data, setData, errors, post, processing } =
    useForm(initialFormValues);

  function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    post("/integrations", {
      headers: {
        "Accept-Language": i18n.language,
      },
    });
  }

  const isCouponFieldVisible =
    (activeType === IntegrationType.SearchApi ||
      activeType === IntegrationType.Widgets) &&
    basicSubscriptionIds.some((id) => data.subscriptionId === id);

  const integrationTypesInfo = useIntegrationTypesInfo();
  const pricingPlans = useGetPricingPlans(data.integrationType, subscriptions);

  return (
    <Page>
      <div className="inline-flex flex-col gap-5 w-full">
        <Heading level={2}>{t("integration_form.title")}</Heading>
        <p className="mb-5">{t("integration_form.description")}</p>

        <form onSubmit={handleSubmit} className="flex flex-col gap-7">
          <Card title={t("integration_form.type")}>
            <RadioButtonGroup
              orientation="vertical"
              name="integrationType"
              value={data.integrationType}
              options={integrationTypesInfo.map(
                ({ Icon, ...integrationTypeInfo }) => ({
                  value: integrationTypeInfo.type,
                  label: (
                    <RichRadioButton
                      name={integrationTypeInfo.title}
                      description={integrationTypeInfo.description}
                      Icon={Icon}
                    />
                  ),
                })
              )}
              onChange={(value) => {
                setData("integrationType", value as IntegrationType);
                router.get(
                  url.pathname,
                  { type: value },
                  { preserveScroll: true }
                );
              }}
            />
            {errors.integrationType && (
              <span className="text-red-500 mt-3 inline-block">
                {errors.integrationType}
              </span>
            )}
          </Card>

          {pricingPlans.length > 0 && (
            <Card title={t("integration_form.pricing_plan")}>
              <RadioButtonGroup
                orientation="vertical"
                name="subscriptionId"
                value={data.subscriptionId}
                onChange={(value) => setData("subscriptionId", value)}
                options={pricingPlans.map((pricingPlan) => ({
                  value: pricingPlan.id,
                  label: (
                    <RichRadioButton
                      name={`${pricingPlan.title} (${pricingPlan.price})`}
                      description={pricingPlan.description}
                    />
                  ),
                }))}
              />
              {errors.subscriptionId && (
                <span className="text-red-500 mt-3 inline-block">
                  {errors.subscriptionId}
                </span>
              )}
            </Card>
          )}
          <Card>
            <FormElement
              label={t("integration_form.integration_name")}
              labelSize="2xl"
              info={t("integration_form.description_name")}
              component={
                <Input
                  type="text"
                  name="integrationName"
                  value={data.integrationName}
                  onChange={(e) => setData("integrationName", e.target.value)}
                />
              }
              error={errors.integrationName}
            />
          </Card>
          <Card>
            <FormElement
              label={t("integration_form.aim")}
              labelSize="2xl"
              info={t("integration_form.description_aim")}
              component={
                <textarea
                  rows={3}
                  className="appearance-none block w-full rounded-lg bg-white text-gray-700 border border-gray-200 py-3 px-4 leading-tight focus:outline-none focus:border-gray-500"
                  name="description"
                  value={data.description}
                  onChange={(e) => setData("description", e.target.value)}
                />
              }
              error={errors.description}
            />
          </Card>
          <Card
            title={t("integration_form.contact_label_functional")}
            contentStyles="flex flex-col gap-5"
          >
            <div className="flex flex-col gap-5">
              <div className="grid grid-cols-3 max-md:flex max-md:flex-col gap-5 ">
                <FormElement
                  label={t("integration_form.contact.last_name")}
                  component={
                    <Input
                      type="text"
                      name="lastNameFunctionalContact"
                      value={data.lastNameFunctionalContact}
                      onChange={(e) =>
                        setData("lastNameFunctionalContact", e.target.value)
                      }
                      placeholder={t("integration_form.contact.last_name")}
                    />
                  }
                  error={errors.lastNameFunctionalContact}
                />
                <FormElement
                  label={t("integration_form.contact.first_name")}
                  component={
                    <Input
                      type="text"
                      name="firstNameFunctionalContact"
                      value={data.firstNameFunctionalContact}
                      onChange={(e) =>
                        setData("firstNameFunctionalContact", e.target.value)
                      }
                      placeholder={t("integration_form.contact.first_name")}
                    />
                  }
                  error={errors.firstNameFunctionalContact}
                />
                <FormElement
                  label={t("integration_form.contact.email")}
                  component={
                    <Input
                      type="email"
                      name="emailFunctionalContact"
                      value={data.emailFunctionalContact}
                      onChange={(e) =>
                        setData("emailFunctionalContact", e.target.value)
                      }
                      placeholder={t("integration_form.contact.email")}
                    />
                  }
                  error={errors.emailFunctionalContact}
                />
              </div>
            </div>
          </Card>

          <Card
            title={t("integration_form.contact_label_technical")}
            contentStyles="flex flex-col gap-5 mb-5"
          >
            <div className="grid grid-cols-3 max-md:flex max-md:flex-col gap-5">
              <FormElement
                label={t("integration_form.contact.last_name")}
                component={
                  <Input
                    type="text"
                    name="lastNameTechnicalContact"
                    value={data.lastNameTechnicalContact}
                    onChange={(e) =>
                      setData("lastNameTechnicalContact", e.target.value)
                    }
                    placeholder={t("integration_form.contact.last_name")}
                  />
                }
                error={errors.lastNameTechnicalContact}
              />
              <FormElement
                label={t("integration_form.contact.first_name")}
                component={
                  <Input
                    type="text"
                    name="firstNameTechnicalContact"
                    value={data.firstNameTechnicalContact}
                    onChange={(e) =>
                      setData("firstNameTechnicalContact", e.target.value)
                    }
                    placeholder={t("integration_form.contact.first_name")}
                  />
                }
                error={errors.firstNameTechnicalContact}
              />

              <FormElement
                label={t("integration_form.contact.email")}
                component={
                  <Input
                    type="email"
                    name="emailPartner"
                    value={data.emailTechnicalContact}
                    onChange={(e) =>
                      setData("emailTechnicalContact", e.target.value)
                    }
                    placeholder={t("integration_form.contact.email")}
                  />
                }
                error={errors.emailTechnicalContact}
              />
            </div>
          </Card>

          <Card contentStyles="flex flex-col gap-5">
            <FormElement
              label={
                <Trans
                  i18nKey="integration_form.agree"
                  t={t}
                  components={{
                    1: (
                      <Link
                        href={t("integration_form.terms_of_use_link")}
                        className="text-publiq-blue-dark hover:underline"
                      />
                    ),
                    2: (
                      <Link
                        href={t("integration_form.privacy_link")}
                        className="text-publiq-blue-dark hover:underline"
                      />
                    ),
                  }}
                />
              }
              labelPosition="right"
              labelSize="base"
              labelWeight="normal"
              component={
                <input
                  type="checkbox"
                  name="agreement"
                  className="text-publiq-blue-dark focus:ring-publiq-blue-dark rounded-sm"
                  checked={data.agreement === "true"}
                  onChange={() =>
                    setData(
                      "agreement",
                      data.agreement === "true" ? "" : "true"
                    )
                  }
                />
              }
              error={errors.agreement}
            />
            {isCouponFieldVisible && (
              <>
                <FormElement
                  label={t("integration_form.coupon")}
                  labelPosition="right"
                  labelSize="base"
                  labelWeight="normal"
                  component={
                    <input
                      type="checkbox"
                      name="hasCoupon"
                      className="text-publiq-blue-dark focus:ring-publiq-blue-dark rounded-sm"
                      checked={hasCoupon}
                      onChange={() => setHasCoupon((prev) => !prev)}
                    />
                  }
                />
                {hasCoupon && (
                  <FormElement
                    component={
                      <Input
                        type="text"
                        name="coupon"
                        value={data.coupon}
                        onChange={(e) => setData("coupon", e.target.value)}
                        placeholder={t("integration_form.code")}
                      />
                    }
                    error={errors.coupon}
                  />
                )}
              </>
            )}
          </Card>

          <ButtonPrimary type="submit" disabled={processing} className="w-fit">
            {t("integration_form.submit")}
          </ButtonPrimary>
        </form>
      </div>
    </Page>
  );
};

New.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default New;
