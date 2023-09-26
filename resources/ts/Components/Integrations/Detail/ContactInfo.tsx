import React, { useMemo, useState } from "react";
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
import { ButtonPrimary } from "../../ButtonPrimary";
import { Contact, Integration } from "../../../Pages/Integrations/Index";
import { FormDropdown } from "../../FormDropdown";
import { useForm } from "@inertiajs/react";
import { ContactType } from "../../../types/ContactType";
import { ButtonSecondary } from "../../ButtonSecondary";
import { QuestionDialog } from "../../QuestionDialog";
import { Dialog } from "../../Dialog";
import ContributorTable from "../../ContributorTable";

type Props = {
  isMobile: boolean;
} & Integration;

export const ContactInfo = ({ id, contacts, isMobile }: Props) => {
  const { t } = useTranslation();
  const [isDisabled, setIsDisabled] = useState(true);
  const [isAddFormVisible, setIsAddFormVisible] = useState(false);
  const [toBeDeletedId, setToBeDeletedId] = useState("");
  const [toBeEditedId, setToBeEditedId] = useState("");

  const functionalContact = useMemo(
    // We know for sure there is a functional contact
    // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
    () => contacts.find((contact) => contact.type === ContactType.Functional)!,
    [contacts]
  );

  const technicalContact = useMemo(
    // We know for sure there is a technical contact
    // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
    () => contacts.find((contact) => contact.type === ContactType.Technical)!,
    [contacts]
  );

  const contributorContacts = useMemo(
    () =>
      contacts.filter((contact) => contact.type === ContactType.Contributor),
    [contacts]
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
    delete: destroy,
    transform,
    errors: errs,
  } = useForm(initialFormValues);

  transform(
    (data) =>
      ({
        ...data,
        functional: data.functional.changed ? data.functional : undefined,
        technical: data.technical.changed ? data.technical : undefined,
        contributors: data.contributors.filter((c) => c.changed),
      }) as typeof data
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
    destroy(`/integrations/${id}/contacts/${toBeDeletedId}`, {
      preserveScroll: true,
      preserveState: false,
    });
  };

  const handleSaveChanges = () => {
    setIsDisabled(true);
    patch(`/integrations/${id}/contacts`, {
      preserveScroll: true,
      // preserveState: false,
    });
  };

  const foundContributor = useMemo(
    () =>
      data.contributors.find((contributor) => contributor.id === toBeEditedId),
    [data.contributors, toBeEditedId]
  );

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
      >
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
        <Dialog
          isVisible={isAddFormVisible}
          onClose={() => setIsAddFormVisible(false)}
          isFullscreen={isMobile}
          className="gap-5"
        >
          <Heading className="font-semibold" level={3}>
            {t("details.contact_info.new")}
          </Heading>

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
                type="text"
                name="newContributorEmail"
                value={data.newContributorEmail}
                onChange={(e) => setData("newContributorEmail", e.target.value)}
              />
            }
          />
          <div className="flex justify-center gap-2 m-5">
            <ButtonPrimary
              onClick={() => {
                handleSaveChanges();
                setIsAddFormVisible(false);
              }}
              className="p-0"
            >
              {t("details.contact_info.save")}
            </ButtonPrimary>
            <ButtonSecondary onClick={() => setIsAddFormVisible(false)}>
              {t("details.contact_info.cancel")}
            </ButtonSecondary>
          </div>
        </Dialog>
        <ContributorTable>
          {data.contributors.map((contributor) => (
            <tr key={contributor.id} className="bg-white border-b">
              <td className="px-6 py-4">{contributor.lastName}</td>
              <td className="px-6 py-4">{contributor.firstName}</td>
              <td className="px-6 py-4">{contributor.email}</td>
              <td>
                <ButtonIcon
                  icon={faPencil}
                  className="text-icon-gray"
                  onClick={() => setToBeEditedId(contributor.id)}
                />
                <ButtonIcon
                  icon={faTrash}
                  className="text-icon-gray"
                  onClick={() => setToBeDeletedId(contributor.id)}
                />
              </td>
            </tr>
          ))}
        </ContributorTable>
        {foundContributor && (
          <Dialog
            isVisible={!!toBeEditedId}
            onClose={() => setToBeEditedId("")}
            isFullscreen={isMobile}
            className="gap-5"
          >
            <FormElement
              label={`${t("integration_form.contact.last_name")}`}
              error={errors[`contributors.lastName`]}
              component={
                <Input
                  type="text"
                  name={`contributor.lastName`}
                  value={foundContributor?.lastName}
                  onChange={(e) =>
                    changeContact("contributor", {
                      ...foundContributor,
                      lastName: e.target.value,
                    })
                  }
                />
              }
            />
            <FormElement
              label={`${t("integration_form.contact.first_name")}`}
              error={errors[`contributor.firstName`]}
              component={
                <Input
                  type="text"
                  name={`contributor.firstName`}
                  value={foundContributor?.firstName}
                  onChange={(e) =>
                    changeContact("contributor", {
                      ...foundContributor,
                      firstName: e.target.value,
                    })
                  }
                />
              }
            />
            <FormElement
              label={`${t("integration_form.contact.email")}`}
              error={errors[`contributors.email`]}
              component={
                <Input
                  type="email"
                  name={`contributor.email`}
                  value={foundContributor?.email}
                  onChange={(e) =>
                    changeContact("contributor", {
                      ...foundContributor,
                      email: e.target.value,
                    })
                  }
                />
              }
            />
            <ButtonPrimary
              onClick={() => {
                setToBeEditedId("");
                patch(`/integrations/${id}/contacts`, {
                  preserveScroll: true,
                });
              }}
              className="self-center"
            >
              {t("details.save")}
            </ButtonPrimary>
          </Dialog>
        )}

        {!isDisabled && (
          <div className="flex flex-col gap-2 items-center">
            <ButtonPrimary
              onClick={() => {
                setIsDisabled(true);

                patch(`/integrations/${id}/contacts`, {
                  preserveScroll: true,
                });
              }}
            >
              {t("details.save")}
            </ButtonPrimary>
          </div>
        )}
      </FormDropdown>
      <QuestionDialog
        isVisible={!!toBeDeletedId}
        onClose={() => {
          setToBeDeletedId("");
        }}
        question={t("details.contact_info.dialog")}
        onConfirm={handleDeleteContributor}
        onCancel={() => {
          setToBeDeletedId("");
        }}
      ></QuestionDialog>
    </>
  );
};
