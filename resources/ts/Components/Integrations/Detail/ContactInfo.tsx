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

type Props = Integration;

export const ContactInfo = ({ contacts }: Props) => {
  const { t } = useTranslation();
  const [isDisabled, setIsDisabled] = useState(true);

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
        <div className="grid grid-cols-2 gap-3 max-md:grid-cols-1">
          <FormElement
            label={`${t("integration_form.contact.organisation")}`}
            component={
              <Input
                type="text"
                name="organisationFunctionalContact"
                defaultValue="organisation"
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.last_name")}`}
            component={
              <Input
                type="text"
                name="lastNameFunctionalContact"
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
                name="emailFunctionalContact"
                defaultValue="email@com"
                disabled={isDisabled}
              />
            }
          />
        </div>
        <Heading className="font-semibold" level={3}>
          {t("integration_form.contact_label_2")}
        </Heading>
        <div className="grid grid-cols-2 gap-3 max-md:grid-cols-1 ">
          <FormElement
            label={`${t("integration_form.contact.organisation")}`}
            component={
              <Input
                type="text"
                name="organisationTechnicalContact"
                defaultValue="organisation"
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.last_name")}`}
            component={
              <Input
                type="text"
                name="lastNameTechnicalContact"
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
                name="firstNameTechnicalContact"
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
                name="emailTechnialContact"
                defaultValue="email@com"
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
        <div className="grid grid-cols-2 gap-3 max-md:grid-cols-1">
          <FormElement
            label={`${t("integration_form.contact.organisation")}`}
            component={
              <Input
                type="text"
                name="organisationTechnicalContact"
                defaultValue="organisation"
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.last_name")}`}
            component={
              <Input
                type="text"
                name="lastNameTechnicalContact"
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
                name="firstNameTechnicalContact"
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
                name="emailTechnialContact"
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
        <Button onClick={() => setIsDisabled(true)}>{t("details.save")}</Button>
      </div>
    </FormDropdown>
  );
};
