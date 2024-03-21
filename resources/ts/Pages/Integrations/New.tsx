import React, { FormEvent, ReactNode, useMemo } from "react";
import { router, useForm } from "@inertiajs/react";
import Layout from "../../layouts/Layout";
import { Heading } from "../../Components/Heading";
import { FormElement } from "../../Components/FormElement";
import { Input } from "../../Components/Input";
import { TFunction } from "i18next";
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
import { RadioButtonGroup } from "../../Components/RadioButtonGroup";

type PricingPlan = {
  id: string;
  title: string;
  price: string;
  description: string;
};

type Subscription = {
  id: string;
  name: string;
  description: string;
  category: string;
  integration_type: string;
  currency: string;
  price: number;
  fee: number;
  deleted_at: string | null;
  created_at: string;
  updated_at: string;
};

const getPricingPlansForType = (
  t: TFunction,
  integrationType: IntegrationType,
  subscriptions: Subscription[]
) => {
  const getInfoForCategory = (category: string): PricingPlan | undefined => {
    const data = subscriptions.find(
      (sub) =>
        sub.category.toLowerCase() === category &&
        sub.integration_type === integrationType
    );

    if (!data) {
      return undefined;
    }

    return {
      id: data.id,
      title: t(`integration_form.pricing.${category}.title`),
      description: t(
        `integration_form.pricing.${category}.description.${integrationType}`,
        data.description
      ),
      price: t(`integration_form.pricing.${category}.price`, {
        price: Intl.NumberFormat("nl-BE", {
          currency: data.currency,
          style: "currency",
          maximumFractionDigits: 0,
        }).format(data.price),
      }),
    };
  };

  return ["free", "basic", "plus", "custom"]
    .map(getInfoForCategory)
    .filter((info) => !!info?.id) as PricingPlan[];
};

type Props = {
  subscriptions: Subscription[];
};

const New = ({ subscriptions }: Props) => {
  const { t } = useTranslation();
  const { i18n } = useTranslation();

  const entryApiId =
    subscriptions.find(
      (subscription) =>
        subscription.integration_type === IntegrationType.EntryApi
    )?.id ?? "";

  const url = new URL(document.location.href);
  const activeTypeFromUrl = url.searchParams.get("type");
  const activeType = isIntegrationType(activeTypeFromUrl)
    ? activeTypeFromUrl
    : IntegrationType.EntryApi;

  const initialFormValues = {
    integrationType: activeType,
    subscriptionId: activeType === IntegrationType.EntryApi ? entryApiId : "",
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
    couponCode: "",
  };

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

  const integrationTypesInfo = useIntegrationTypesInfo();
  const translatedPricingPlans = useMemo(
    () => getPricingPlansForType(t, data.integrationType, subscriptions),
    [t, data.integrationType, subscriptions]
  );

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
                    <div className="flex flex-row justify-between gap-2">
                      <span className="flex flex-row gap-2 justify-between">
                        <Icon className="h-7 w-7" />
                        <span>{integrationTypeInfo.title}</span>
                      </span>
                      <span className="text-gray-400 font-thin">
                        {integrationTypeInfo.description}
                      </span>
                    </div>
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

          {translatedPricingPlans.length > 0 &&
            activeType !== IntegrationType.EntryApi && (
              <Card title={t("integration_form.pricing_plan")}>
                <RadioButtonGroup
                  orientation="vertical"
                  name="subscriptionId"
                  value={data.subscriptionId}
                  onChange={(value) => setData("subscriptionId", value)}
                  options={translatedPricingPlans.map((pricingPlan) => ({
                    value: pricingPlan.id,
                    label: (
                      <div className="flex flex-row items-center justify-between gap-2">
                        <span className="text-left w-1/2">
                          {pricingPlan.title} ({pricingPlan.price})
                        </span>
                        <span className="text-gray-400 font-thin text-right">
                          {pricingPlan.description}
                        </span>
                      </div>
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
              labelSize="xl"
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
              labelSize="xl"
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
            <FormElement
              label={t("integration_form.coupon")}
              labelSize="base"
              labelWeight="normal"
              labelPosition="right"
              component={
                <input
                  type="checkbox"
                  name="coupon"
                  className="text-publiq-blue-dark focus:ring-publiq-blue-dark rounded-sm"
                  checked={data.coupon === "true"}
                  onChange={() =>
                    setData("coupon", data.coupon === "true" ? "" : "true")
                  }
                />
              }
              error={errors.coupon}
            />
            {data.coupon && (
              <FormElement
                component={
                  <Input
                    type="text"
                    name="couponCode"
                    value={data.couponCode}
                    onChange={(e) => setData("couponCode", e.target.value)}
                    placeholder={t("integration_form.code")}
                  />
                }
                error={errors.couponCode}
              />
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
