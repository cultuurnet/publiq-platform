import React, { useState } from "react";
import { Heading } from "../../Heading";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonIcon } from "../../ButtonIcon";
import { faPlus } from "@fortawesome/free-solid-svg-icons";
import { ButtonSecondary } from "../../ButtonSecondary";
import { useTranslation } from "react-i18next";
import { Button } from "../../Button";
import { Integration } from "../../../Pages/Integrations/Index";
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
  const contributerContacts = contacts.filter(
    (contact) => contact.type === ContactType.Contributor
  );

  console.log({ functionalContact, technicalContact, contributerContacts });

  const initialFormValues = {
    lastNameFunctionalContact: functionalContact.lastName,
    firstNameFunctionalContact: functionalContact.firstName,
    emailFunctionalContact: functionalContact.email,
    lastNameTechnicalContact: technicalContact.lastName,
    firstNameTechnicalContact: technicalContact.firstName,
    emailTechnicalContact: technicalContact.email,
  };

  const { data, setData, patch } = useForm(initialFormValues);

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
            component={
              <Input
                type="text"
                name="lastNameFunctionalContact"
                value={data.lastNameFunctionalContact}
                onChange={(e) =>
                  setData("lastNameFunctionalContact", e.target.value)
                }
                disabled={isDisabled}
              />
            }
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
                disabled={isDisabled}
              />
            }
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
            component={
              <Input
                type="text"
                name="lastNameTechnicalContact"
                value={data.lastNameTechnicalContact}
                onChange={(e) =>
                  setData("lastNameTechnicalContact", e.target.value)
                }
                disabled={isDisabled}
              />
            }
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
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.email")}`}
            component={
              <Input
                type="email"
                name="emailTechnialContact"
                value={data.emailTechnicalContact}
                onChange={(e) =>
                  setData("emailTechnicalContact", e.target.value)
                }
                disabled={isDisabled}
              />
            }
          />
        </div>
        <div className="flex items-start"></div>
        <div className="flex gap-2 items-center">
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
        </div>
        <ButtonSecondary className="self-start">
          {t("details.contact_info.delete")}
        </ButtonSecondary>
      </div>
      <div className="flex flex-col gap-2 items-center">
        <Button
          onClick={() => {
            setIsDisabled(true);

            patch(`/integrations/${id}`, {
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
