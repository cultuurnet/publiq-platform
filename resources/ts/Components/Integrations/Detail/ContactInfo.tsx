import React, { useState } from "react";
import { Heading } from "../../Heading";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonIcon } from "../../ButtonIcon";
import { faPlus } from "@fortawesome/free-solid-svg-icons";
import { ButtonSecondary } from "../../ButtonSecondary";
import { useTranslation } from "react-i18next";
import { Button } from "../../Button";
import { Contact, Integration } from "../../../Pages/Integrations/Index";
import { FormDropdown } from "../../FormDropdown";
import { useForm } from "@inertiajs/react";
import { ContactType } from "../../../types/ContactType";

type Props = Integration;

export const ContactInfo = ({ id, contacts }: Props) => {
  const { t } = useTranslation();
  const [isDisabled, setIsDisabled] = useState(true);

  // We know for sure there is a functional contact
  // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
  const functionalContact = contacts.find(
    (contact) => contact.type === ContactType.Functional
  )!;
  // We know for sure there is a technical contact
  // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
  const technicalContact = contacts.find(
    (contact) => contact.type === ContactType.Technical
  )!;
  const contributorContacts = contacts.filter(
    (contact) => contact.type === ContactType.Contributor
  );

  const initialFormValues = {
    functional: { ...functionalContact, changed: false },
    technical: { ...technicalContact, changed: false },
    contributors: contributorContacts.map((contributor) => ({
      ...contributor,
      changed: false,
    })),
  };

  const { data, setData, patch, errors: errs } = useForm(initialFormValues);

  const errors = errs as Record<string, string | undefined>;

  console.log("errors", { errors });

  const changeContact = (type: ContactType, newData: Contact) => {
    const property = type === "contributor" ? "contributors" : type;

    setData((prevData) => ({
      ...prevData,
      [property]:
        property === "contributors"
          ? prevData.contributors.map((contributor) => {
              if (contributor.id === newData.id) {
                return {
                  ...newData,
                  changed: true,
                };
              }

              return contributor;
            })
          : { ...newData, changed: true },
    }));
  };

  return (
    <FormDropdown
      title={t("details.contact_info.title")}
      disabled={isDisabled}
      onChangeDisabled={(newDisabled) => {
        setIsDisabled(newDisabled);
      }}
    >
      <div className="flex flex-col gap-5">
        <Heading className="font-semibold" level={3}>
          {t("integration_form.contact_label_1")}
        </Heading>
        <div className="grid grid-cols-3 gap-3 max-md:grid-cols-1">
          <FormElement
            label={`${t("integration_form.contact.last_name")}`}
            error={errors["functional.lastName"]}
            component={
              <Input
                type="text"
                name="functional.lastName"
                value={data.functional.lastName}
                onChange={(e) =>
                  changeContact("functional", {
                    ...data.functional,
                    lastName: e.target.value,
                  })
                }
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.first_name")}`}
            error={errors["functional.firstName"]}
            component={
              <Input
                type="text"
                name="functional.firstName"
                value={data.functional.firstName}
                onChange={(e) =>
                  changeContact("functional", {
                    ...data.functional,
                    firstName: e.target.value,
                  })
                }
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.email")}`}
            error={errors["functional.email"]}
            component={
              <Input
                type="email"
                name="functional.email"
                value={data.functional.email}
                onChange={(e) =>
                  changeContact("functional", {
                    ...data.functional,
                    email: e.target.value,
                  })
                }
                disabled={isDisabled}
              />
            }
          />
        </div>
        <Heading className="font-semibold" level={3}>
          {t("integration_form.contact_label_2")}
        </Heading>
        <div className="grid grid-cols-3 gap-3 max-md:grid-cols-1 ">
          <FormElement
            label={`${t("integration_form.contact.last_name")}`}
            error={errors["technical.lastName"]}
            component={
              <Input
                type="text"
                name="technical.lastName"
                value={data.technical.lastName}
                onChange={(e) =>
                  changeContact("technical", {
                    ...data.technical,
                    lastName: e.target.value,
                  })
                }
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.first_name")}`}
            error={errors["technical.firstName"]}
            component={
              <Input
                type="text"
                name="technical.firstName"
                value={data.technical.firstName}
                onChange={(e) =>
                  changeContact("technical", {
                    ...data.technical,
                    firstName: e.target.value,
                  })
                }
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.email")}`}
            error={errors["technical.email"]}
            component={
              <Input
                type="email"
                name="technical.email"
                value={data.technical.email}
                onChange={(e) =>
                  changeContact("technical", {
                    ...data.technical,
                    email: e.target.value,
                  })
                }
                disabled={isDisabled}
              />
            }
          />
        </div>
        <div className="flex items-start"></div>
        {/* <div className="flex gap-2 items-center">
          <Heading className="font-semibold" level={3}>
            {t("integration_form.contact_label_3")}
          </Heading>
          <ButtonIcon
            className="flex gap-2 items-center"
            icon={faPlus}
          ></ButtonIcon>
        </div>
        <div className="grid grid-cols-3 gap-3 max-md:grid-cols-1">
          <FormElement
            label={`${t("integration_form.contact.last_name")}`}
            component={
              <Input
                type="text"
                name="lastNameContact"
                defaultValue="Lastname"
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.first_name")}`}
            component={
              <Input
                type="text"
                name="firstNameContact"
                defaultValue="FirstName"
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.email")}`}
            component={
              <Input
                type="email"
                name="emailContact"
                defaultValue="email@com"
                disabled={isDisabled}
              />
            }
          />
        </div> */}
        <ButtonSecondary className="self-start">
          {t("details.contact_info.delete")}
        </ButtonSecondary>
      </div>
      <div className="flex flex-col gap-2 items-center">
        <Button
          onClick={() => {
            setIsDisabled(true);

            patch(`/integrations/${id}/contacts`, {
              preserveScroll: true,
            });
          }}
        >
          {t("details.save")}
        </Button>
      </div>
    </FormDropdown>
  );
};
