import React, { FormEvent, ReactNode, useMemo } from "react";
import { useForm } from "@inertiajs/react";
import Layout from "../../layouts/Layout";
import { Heading } from "../../Components/Heading";
import { FormElement } from "../../Components/FormElement";
import { Input } from "../../Components/Input";
import { TFunction } from "i18next";
import { Trans, useTranslation } from "react-i18next";
import { Card } from "../../Components/Card";
import { ButtonPrimary } from "../../Components/ButtonPrimary";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronLeft } from "@fortawesome/free-solid-svg-icons";
import { Page } from "../../Components/Page";
import { ButtonLinkSecondary } from "../../Components/ButtonLinkSecondary";
import { useTranslateRoute } from "../../hooks/useTranslateRoute";
import { Link } from "../../Components/Link";
import { useIntegrationTypes } from "../../Components/IntegrationTypes";

const pricing = (t: TFunction, subscriptions: Subscription[]) => {
  const getInfoForType = (type: string) => {
    // All types should match with a category
     
    const data = subscriptions.find(
      (sub) => sub.category.toLowerCase() === type
    )!;

    return {
      id: data.id,
      price: data.price,
      currency: data.currency,
    };
  };

  const basic = getInfoForType("basic");
  const plus = getInfoForType("plus");
  const custom = getInfoForType("custom");

  return [
    {
      id: basic.id,
      title: t("integration_form.pricing.basic.title"),
      description: t("integration_form.pricing.basic.description"),
      price: t("integration_form.pricing.basic.price", {
        price: basic.price,
        currency: basic.currency,
      }),
    },
    {
      id: plus.id,
      title: t("integration_form.pricing.plus.title"),
      description: t("integration_form.pricing.plus.description"),
      price: t("integration_form.pricing.plus.price", {
        price: plus.price,
        currency: plus.currency,
      }),
    },
    {
      id: custom.id,
      title: t("integration_form.pricing.custom.title"),
      description: t("integration_form.pricing.custom.description"),
      price: t("integration_form.pricing.custom.price", {
        price: custom.price,
        currency: custom.currency,
      }),
    },
  ];
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

type Props = {
  subscriptions: Subscription[];
};

const New = ({ subscriptions }: Props) => {
  const { t } = useTranslation();
  const { i18n } = useTranslation();
  const translateRoute = useTranslateRoute();

  const url = new URL(document.location.href);
  const activeType = url.searchParams.get("type") ?? "";

  const initialFormValues = {
    integrationType: activeType,
    subscriptionId: "",
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

  const translatedIntegrations = useIntegrationTypes();
  const translatedPricing = useMemo(
    () => pricing(t, subscriptions),
    [t, subscriptions]
  );

  return (
    <Page>
      <div className="inline-flex flex-col gap-5">
        <ButtonLinkSecondary
          href={translateRoute("/integrations")}
          className="w-fit"
        >
          <FontAwesomeIcon icon={faChevronLeft}></FontAwesomeIcon>
          <span>{t("integration_form.back")}</span>
        </ButtonLinkSecondary>
        <Heading level={2}>{t("integration_form.title")}</Heading>
        <p className="mb-5">{t("integration_form.description")}</p>

        <form onSubmit={handleSubmit} className="flex flex-col gap-7">
          <FormElement
            label={t("integration_form.type")}
            labelSize="xl"
            component={
              <div className="md:grid md:grid-cols-3 gap-5 max-md:flex max-md:flex-col max-md:items-center pb-3">
                {translatedIntegrations.map((integration) => (
                  <Card
                    active={data.integrationType === integration.type}
                    {...integration}
                    className="rounded-lg"
                    role="button"
                    key={integration.type}
                    img={integration.image}
                    onClick={() => {
                      setData("integrationType", integration.type);
                    }}
                    textCenter
                  ></Card>
                ))}
              </div>
            }
            error={errors.integrationType}
          />

          <FormElement
            label={t("integration_form.pricing_plan")}
            labelSize="xl"
            component={
              <div className="md:grid md:grid-cols-3 gap-5 max-md:flex max-md:flex-col max-md:items-center pb-3">
                {translatedPricing.map((pricing) => (
                  <Card
                    role="button"
                    key={pricing.title}
                    onClick={() => {
                      setData("subscriptionId", pricing.id);
                    }}
                    {...pricing}
                    active={data.subscriptionId === pricing.id}
                    className="rounded-lg"
                    contentStyles="font-bold"
                    textCenter
                  >
                    {pricing.price}
                  </Card>
                ))}
              </div>
            }
            error={errors.subscriptionId}
          />
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
          <FormElement
            label={t("integration_form.aim")}
            labelSize="xl"
            info={t("integration_form.description_aim")}
            component={
              <textarea
                rows={3}
                className="appearance-none block w-full rounded-lg bg-gray-200 text-gray-700 border border-gray-200 py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                name="description"
                value={data.description}
                onChange={(e) => setData("description", e.target.value)}
              />
            }
            error={errors.description}
          />
          <div className="flex flex-col gap-5">
            <Heading className="font-semibold" level={3}>
              {t("integration_form.contact_label_functional")}
            </Heading>
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
          </div>

          <div className="flex flex-col gap-5 mb-5">
            <Heading className="font-semibold" level={3}>
              {t("integration_form.contact_label_technical")}
            </Heading>
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
          </div>

          <div className="flex flex-col gap-5">
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
              className=""
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
          </div>

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
