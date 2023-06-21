import React, { FormEvent, ReactNode, useMemo } from "react";
import { useForm } from "@inertiajs/react";
import Layout from "../../Shared/Layout";
import { Heading } from "../../Shared/Heading";
import { FormElement } from "../../Shared/FormElement";
import { Input } from "../../Shared/Input";
import { TFunction } from "i18next";
import { useTranslation } from "react-i18next";
import { Card } from "../../Shared/Card";
import { Button } from "../../Shared/Button";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronLeft } from "@fortawesome/free-solid-svg-icons";
import { Page } from "../../Shared/Page";
import { ButtonLinkSecondary } from "../../Shared/ButtonLinkSecondary";
import { IntegrationType } from "../../types/IntegrationType";
import { useTranslateRoute } from "../../hooks/useTranslateRoute";

const integrationTypes = (t: TFunction) => [
  {
    type: IntegrationType.EntryApi,
    title: t("home.integration_types.entry_api.title"),
    description: t("home.integration_types.entry_api.description"),
    img: "",
  },
  {
    type: IntegrationType.SearchApi,
    title: t("home.integration_types.search_api.title"),
    description: t("home.integration_types.search_api.description"),
    img: "",
  },
  {
    type: IntegrationType.Widgets,
    title: t("home.integration_types.widgets.title"),
    description: t("home.integration_types.widgets.description"),
    img: "",
  },
];

const pricing = (t: TFunction, subscriptions: Subscription[]) => {
  const getInfoForType = (type: string) => {
    const data = subscriptions.find(
      (sub) => sub.category.toLowerCase() === type
    );

    // TODO: Remove defaults once all subscriptions have been added in the backend
    return {
      id: data?.id,
      price: data?.price ?? "Te bepalen",
      currency: data?.currency ?? "EUR",
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

const initialFormValues = {
  integrationType: "",
  subscriptionId: "",
  integrationName: "",
  description: "",
  organisationFunctionalContact: "",
  firstNameFunctionalContact: "",
  lastNameFunctionalContact: "",
  emailFunctionalContact: "",
  organisationTechnicalContact: "",
  firstNameTechnicalContact: "",
  lastNameTechnicalContact: "",
  emailTechnicalContact: "",
  agreement: "",
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

const Index = ({ subscriptions }: Props) => {
  const { t } = useTranslation();
  const { data, setData, errors, post, processing } =
    useForm(initialFormValues);
  const { i18n } = useTranslation();
  const translateRoute = useTranslateRoute();

  function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    post("/integrations", {
      headers: {
        "Accept-Language": i18n.language,
      },
    });
  }

  const translatedIntegrations = useMemo(() => integrationTypes(t), [t]);
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

        <form onSubmit={handleSubmit} className="flex flex-col gap-5">
          <FormElement
            label={`${t("integration_form.type")}`}
            labelSize="xl"
            component={
              <div className="flex gap-5 max-md:flex-col max-md:items-center md:flex-row pb-3">
                {translatedIntegrations.map((integration) => (
                  <button
                    type="button"
                    key={integration.type}
                    onClick={() => {
                      setData("integrationType", integration.type);
                    }}
                  >
                    <Card
                      active={data.integrationType === integration.type}
                      {...integration}
                      className="w-full md:min-h-[27rem]"
                    ></Card>
                  </button>
                ))}
              </div>
            }
            error={errors.integrationType}
          />

          <FormElement
            label={`${t("integration_form.pricing_plan")}`}
            labelSize="xl"
            component={
              <div className="flex gap-5 max-md:flex-col max-md:items-center md:flex-row pb-3">
                {translatedPricing.map((pricing) => (
                  <button
                    type="button"
                    className="w-full"
                    key={pricing.title}
                    onClick={() => {
                      setData("subscriptionId", pricing.id);
                    }}
                  >
                    <Card
                      {...pricing}
                      active={data.subscriptionId === pricing.title}
                      className=" md:min-h-[12rem]"
                    >
                      {pricing.price}
                    </Card>
                  </button>
                ))}
              </div>
            }
            error={errors.subscriptionId}
          />
          <FormElement
            label={`${t("integration_form.integration_name")}`}
            labelSize="xl"
            info={`${t("integration_form.description_name")}`}
            className="md:w-[65%]"
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
            label={`${t("integration_form.aim")}`}
            labelSize="xl"
            info={`${t("integration_form.description_aim")}`}
            component={
              <textarea
                rows={3}
                className="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                name="description"
                value={data.description}
                onChange={(e) => setData("description", e.target.value)}
              />
            }
            error={errors.description}
          />
          <div className="flex flex-col gap-5">
            <Heading className="font-semibold" level={3}>
              {t("integration_form.contact_label_1")}
            </Heading>
            <div className="flex flex-col gap-5">
              <div className="flex">
                <FormElement
                  label={`${t("integration_form.contact.organisation")}`}
                  component={
                    <Input
                      type="text"
                      name="organisationFunctionalContact"
                      className="md:w-[32%]"
                      value={data.organisationFunctionalContact}
                      onChange={(e) =>
                        setData("organisationFunctionalContact", e.target.value)
                      }
                      placeholder={`${t(
                        "integration_form.contact.organisation"
                      )}`}
                    />
                  }
                  error={errors.organisationFunctionalContact}
                />
              </div>
              <div className="flex max-md:flex-col gap-5 ">
                <FormElement
                  label={`${t("integration_form.contact.last_name")}`}
                  component={
                    <Input
                      type="text"
                      name="lastNameFunctionalContact"
                      value={data.lastNameFunctionalContact}
                      onChange={(e) =>
                        setData("lastNameFunctionalContact", e.target.value)
                      }
                      placeholder={`${t("integration_form.contact.last_name")}`}
                    />
                  }
                  error={errors.lastNameFunctionalContact}
                />
                <FormElement
                  label={`${t("integration_form.contact.first_name")}`}
                  component={
                    <Input
                      type="text"
                      name="firstNameFunctionalContact"
                      value={data.firstNameFunctionalContact}
                      onChange={(e) =>
                        setData("firstNameFunctionalContact", e.target.value)
                      }
                      placeholder={`${t(
                        "integration_form.contact.first_name"
                      )}`}
                    />
                  }
                  error={errors.firstNameFunctionalContact}
                />
                <FormElement
                  label={`${t("integration_form.contact.email")}`}
                  component={
                    <Input
                      type="email"
                      name="emailFunctionalContact"
                      value={data.emailFunctionalContact}
                      onChange={(e) =>
                        setData("emailFunctionalContact", e.target.value)
                      }
                      placeholder={`${t("integration_form.contact.email")}`}
                    />
                  }
                  error={errors.emailFunctionalContact}
                />
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-5 mb-5">
            <Heading className="font-semibold" level={3}>
              {t("integration_form.contact_label_2")}
            </Heading>
            <div className="flex flex-col gap-5">
              <div className="flex">
                <FormElement
                  label={`${t("integration_form.contact.organisation")}`}
                  component={
                    <Input
                      type="text"
                      className="md:w-[32%]"
                      name="organisationTechnicalContact"
                      value={data.organisationTechnicalContact}
                      onChange={(e) =>
                        setData("organisationTechnicalContact", e.target.value)
                      }
                      placeholder={`${t(
                        "integration_form.contact.organisation"
                      )}`}
                    />
                  }
                  error={errors.organisationTechnicalContact}
                />
              </div>
              <div className="flex basis-1/2 max-md:flex-col gap-5">
                <FormElement
                  label={`${t("integration_form.contact.last_name")}`}
                  component={
                    <Input
                      type="text"
                      name="lastNameTechnicalContact"
                      value={data.lastNameTechnicalContact}
                      onChange={(e) =>
                        setData("lastNameTechnicalContact", e.target.value)
                      }
                      placeholder={`${t("integration_form.contact.last_name")}`}
                    />
                  }
                  error={errors.lastNameTechnicalContact}
                />
                <FormElement
                  label={`${t("integration_form.contact.first_name")}`}
                  component={
                    <Input
                      type="text"
                      name="firstNameTechnicalContact"
                      value={data.firstNameTechnicalContact}
                      onChange={(e) =>
                        setData("firstNameTechnicalContact", e.target.value)
                      }
                      placeholder={`${t(
                        "integration_form.contact.first_name"
                      )}`}
                    />
                  }
                  error={errors.firstNameTechnicalContact}
                />

                <FormElement
                  label={`${t("integration_form.contact.email")}`}
                  component={
                    <Input
                      type="emailTechnicalContact"
                      name="emailPartner"
                      value={data.emailTechnicalContact}
                      onChange={(e) =>
                        setData("emailTechnicalContact", e.target.value)
                      }
                      placeholder={`${t("integration_form.contact.email")}`}
                    />
                  }
                  error={errors.emailTechnicalContact}
                />
              </div>
            </div>
          </div>

          <div>
            <FormElement
              label={`${t("integration_form.agree")} ${t(
                "integration_form.terms_of_use"
              )}`}
              labelPosition="right"
              className=""
              component={
                <input
                  type="checkbox"
                  name="agreement"
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
          </div>

          <Button type="submit" disabled={processing} className="w-fit">
            {t("integration_form.submit")}
          </Button>
        </form>
      </div>
    </Page>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
