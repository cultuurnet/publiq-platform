import React, { useState } from "react";
import { Heading } from "../../Heading";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonIcon } from "../../ButtonIcon";
import {
  faPencil,
  faPlus,
  faTrash,
  faFloppyDisk,
} from "@fortawesome/free-solid-svg-icons";
import { useTranslation } from "react-i18next";
import { Button } from "../../Button";
import { Contact, Integration } from "../../../Pages/Integrations/Index";
import { FormDropdown } from "../../FormDropdown";
import { useForm } from "@inertiajs/react";
import { ContactType } from "../../../types/ContactType";
import { ButtonSecondary } from "../../ButtonSecondary";
import { router } from "@inertiajs/react";
import { QuestionDialog } from "../../QuestionDialog";
import { useSectionCollapsedContext } from "../../../context/SectionCollapsedContext";

type Props = Integration;

export const ContactInfo = ({ id, contacts }: Props) => {
  const { t } = useTranslation();
  const [isDisabled, setIsDisabled] = useState(true);
  const [isAddFormVisible, setIsAddFormVisible] = useState(false);
  const [isDeleteDialogVisible, setIsDeleteDialogVisible] = useState(false);
  const [toBeDeletedId, setToBeDeletedId] = useState("");

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
    newContributorLastName: "",
    newContributorFirstName: "",
    newContributorEmail: "",
  };

  const {
    data,
    setData,
    patch,
    transform,
    errors: errs,
  } = useForm(initialFormValues);

  const [collapsed, setCollapsed] = useSectionCollapsedContext();

  transform(
    (data) =>
      ({
        ...data,
        functional: data.functional.changed ? data.functional : undefined,
        technical: data.technical.changed ? data.technical : undefined,
        contributors: data.contributors.filter((c) => c.changed),
      } as typeof data)
  );

  const errors = errs as Record<string, string | undefined>;

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

  const handleDeleteContributor = () => {
    router.delete(`/integrations/${id}/contacts/${toBeDeletedId}`, {
      preserveScroll: true,
    });
  };

  const handleSaveChanges = () => {
    setIsDisabled(true);
    patch(`/integrations/${id}/contacts`, {
      preserveScroll: true,
      preserveState: false,
    });
  };

  return (
    <>
      <FormDropdown
        title={t("details.contact_info.title")}
        actions={
          isDisabled ? (
            <ButtonIcon
              icon={faPencil}
              className="text-icon-gray"
              onClick={() => setIsDisabled((prev) => !prev)}
            />
          ) : (
            <ButtonIcon
              icon={faFloppyDisk}
              className="text-icon-gray"
              onClick={handleSaveChanges}
            />
          )
        }
        isCollapsed={collapsed.contacts}
        onChangeCollapsed={(newValue) =>
          setCollapsed((prev) => ({ ...prev, contacts: newValue }))
        }
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

          <div className="flex gap-2 items-center min-h-[3rem]">
            <Heading className="font-semibold" level={3}>
              {t("integration_form.contact_label_3")}
            </Heading>
            {!isAddFormVisible && (
              <ButtonIcon
                className="flex gap-2 items-center"
                icon={faPlus}
                onClick={() => {
                  setIsAddFormVisible(true);
                  setIsDisabled(true);
                }}
              ></ButtonIcon>
            )}
          </div>

          {isAddFormVisible && (
            <>
              <div className="flex flex-col gap-4 shadow p-4">
                <Heading className="font-semibold" level={3}>
                  {t("details.contact_info.new")}
                </Heading>

                <div className="grid grid-cols-3 gap-3 max-md:grid-cols-1">
                  <FormElement
                    label={`${t("integration_form.contact.last_name")}`}
                    error={errors["newContributorLastName"]}
                    component={
                      <Input
                        type="text"
                        name="newContributorLastName"
                        value={data.newContributorLastName}
                        onChange={(e) =>
                          setData("newContributorLastName", e.target.value)
                        }
                      />
                    }
                  />
                  <FormElement
                    label={`${t("integration_form.contact.first_name")}`}
                    error={errors["newContributorFirstName"]}
                    component={
                      <Input
                        type="text"
                        name="newContributorFirstName"
                        value={data.newContributorFirstName}
                        onChange={(e) =>
                          setData("newContributorFirstName", e.target.value)
                        }
                      />
                    }
                  />
                  <FormElement
                    label={`${t("integration_form.contact.email")}`}
                    error={errors["newContributorEmail"]}
                    component={
                      <Input
                        type="newContributorEmail"
                        name="newContributorEmail"
                        value={data.newContributorEmail}
                        onChange={(e) =>
                          setData("newContributorEmail", e.target.value)
                        }
                      />
                    }
                  />
                </div>
                <div className="flex justify-center gap-2">
                  <Button onClick={handleSaveChanges} className="p-0">
                    {t("details.contact_info.save")}
                  </Button>
                  <ButtonSecondary onClick={() => setIsAddFormVisible(false)}>
                    {t("details.contact_info.cancel")}
                  </ButtonSecondary>
                </div>
              </div>
            </>
          )}
          {data.contributors.map((contributor, index) => (
            <div
              key={contributor.id}
              className="flex flex-col gap-4 shadow p-4"
            >
              <div className="flex justify-between items-center">
                <Heading className="font-semibold" level={3}>{`Medewerker #${
                  index + 1
                }`}</Heading>
                <ButtonIcon
                  icon={faTrash}
                  size="lg"
                  className="text-icon-gray self-end"
                  onClick={() => {
                    setToBeDeletedId(contributor.id);
                    setIsDeleteDialogVisible(true);
                  }}
                />
              </div>
              <div className="grid grid-cols-3 gap-3 max-md:grid-cols-1">
                <FormElement
                  label={`${t("integration_form.contact.last_name")}`}
                  error={errors[`contributors.${index}.lastName`]}
                  component={
                    <Input
                      type="text"
                      name={`contributors.${index}.lastName`}
                      value={contributor.lastName}
                      onChange={(e) =>
                        changeContact("contributor", {
                          ...contributor,
                          lastName: e.target.value,
                        })
                      }
                      disabled={isDisabled}
                    />
                  }
                />
                <FormElement
                  label={`${t("integration_form.contact.first_name")}`}
                  error={errors[`contributors.${index}.firstName`]}
                  component={
                    <Input
                      type="text"
                      name={`contributors.${index}.firstName`}
                      value={contributor.firstName}
                      onChange={(e) =>
                        changeContact("contributor", {
                          ...contributor,
                          firstName: e.target.value,
                        })
                      }
                      disabled={isDisabled}
                    />
                  }
                />
                <FormElement
                  label={`${t("integration_form.contact.email")}`}
                  error={errors[`contributors.${index}.email`]}
                  component={
                    <Input
                      type="email"
                      name={`contributors.${index}.email`}
                      value={contributor.email}
                      onChange={(e) =>
                        changeContact("contributor", {
                          ...contributor,
                          email: e.target.value,
                        })
                      }
                      disabled={isDisabled}
                    />
                  }
                />
              </div>
            </div>
          ))}
          {/* <ButtonSecondary className="self-start">
          {t("details.contact_info.delete")}
        </ButtonSecondary> */}
        </div>
        {!isDisabled && (
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
        )}
      </FormDropdown>
      <QuestionDialog
        isVisible={isDeleteDialogVisible}
        onClose={() => {
          setIsDeleteDialogVisible((prev) => !prev);
        }}
        question={t("integrations.dialog.delete")}
        onConfirm={handleDeleteContributor}
        onCancel={() => {
          setIsDeleteDialogVisible(false);
          setToBeDeletedId("");
        }}
      ></QuestionDialog>
    </>
  );
};
