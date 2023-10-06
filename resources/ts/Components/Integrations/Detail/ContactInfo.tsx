import React, { useMemo, useState } from "react";
import { Heading } from "../../Heading";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "../../ButtonPrimary";
import { Contact, Integration } from "../../../Pages/Integrations/Index";
import { FormDropdown } from "../../FormDropdown";
import { useForm } from "@inertiajs/react";
import { ContactType } from "../../../types/ContactType";
import { ButtonSecondary } from "../../ButtonSecondary";
import { QuestionDialog } from "../../QuestionDialog";
import { Dialog } from "../../Dialog";
import { ContactsTable } from "../../ContactsTable";
import { classNames } from "../../../utils/classNames";

export type ContactFormData = {
  functional: Contact;
  technical: Contact;
  contributors: Contact[];
  newContributorLastName: string;
  newContributorFirstName: string;
  newContributorEmail: string;
};

type Props = {
  isMobile: boolean;
} & Integration;

export const ContactInfo = ({ id, contacts, isMobile }: Props) => {
  const { t } = useTranslation();
  const [isDisabled, setIsDisabled] = useState(true);
  const [isAddFormVisible, setIsAddFormVisible] = useState(false);
  const [toBeDeletedId, setToBeDeletedId] = useState("");
  const [toBeDeletedEmail, setToBeDeletedEmail] = useState("")
  const [toBeEditedId, setToBeEditedId] = useState("");
  const [isMobileContactVisible, setIsMobileContactVisible] = useState(false);

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
      preserveState: false,
    });
  };

  const foundContributor = useMemo(
    () =>
      data.contributors.find((contributor) => contributor.id === toBeEditedId),
    [data.contributors, toBeEditedId]
  );

  const [formContactType, setFormContactType] = useState("" as ContactType);

  const toBeEditedContact = useMemo(() => {
    if (functionalContact.id === toBeEditedId) {
      setFormContactType(ContactType.Functional);
      return data.functional;
    }
    if (technicalContact.id === toBeEditedId) {
      setFormContactType(ContactType.Technical);
      return data.technical;
    }
    if (foundContributor) {
      setFormContactType(ContactType.Contributor);
      return foundContributor;
    }
  }, [
    functionalContact,
    technicalContact,
    foundContributor,
    data.functional,
    data.technical,
    toBeEditedId,
  ]);

  return (
    <>
      <FormDropdown title={t("details.contact_info.title")}>
        <Dialog
          isVisible={isAddFormVisible}
          onClose={() => setIsAddFormVisible(false)}
          isFullscreen={isMobile}
          contentStyles="gap-5"
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
        <ContactsTable
          data={data}
          onEdit={(id) => setToBeEditedId(id)}
          onDelete={(id, email) => {setToBeDeletedId(id); setToBeDeletedEmail(email);}}
          onPreview={(bool) => setIsMobileContactVisible(bool)}
          functionalId={functionalContact.id}
          technicalId={technicalContact.id}
        />
        <div className="flex gap-2 items-center min-h-[3rem]">
          {!isAddFormVisible && (
            <ButtonPrimary
              onClick={() => {
                setIsAddFormVisible(true);
                setIsDisabled(true);
              }}
            >
              {t("integration_form.add")}
            </ButtonPrimary>
          )}
        </div>
        {toBeEditedContact && (
          <Dialog
            isVisible={!!toBeEditedId}
            onClose={() => {
              setToBeEditedId("");
              setIsMobileContactVisible(false);
            }}
            isFullscreen={isMobile}
            contentStyles="gap-5"
          >
            <FormElement
              label={`${t("integration_form.contact.last_name")}`}
              error={errors[`${formContactType}.lastName`]}
              component={
                <Input
                  type="text"
                  name={`${formContactType}.lastName`}
                  value={toBeEditedContact?.lastName}
                  onChange={(e) =>
                    changeContact(formContactType, {
                      ...toBeEditedContact,
                      lastName: e.target.value,
                    })
                  }
                  disabled={isMobileContactVisible}
                />
              }
            />
            <FormElement
              label={`${t("integration_form.contact.first_name")}`}
              error={errors[`${formContactType}.firstName`]}
              component={
                <Input
                  type="text"
                  name={`${formContactType}.firstName`}
                  value={toBeEditedContact?.firstName}
                  onChange={(e) =>
                    changeContact(formContactType, {
                      ...toBeEditedContact,
                      firstName: e.target.value,
                    })
                  }
                  disabled={isMobileContactVisible}
                />
              }
            />
            <FormElement
              label={`${t("integration_form.contact.email")}`}
              error={errors[`${formContactType}.email`]}
              component={
                <Input
                  type="email"
                  name={`${formContactType}.email`}
                  value={toBeEditedContact?.email}
                  onChange={(e) =>
                    changeContact(formContactType, {
                      ...toBeEditedContact,
                      email: e.target.value,
                    })
                  }
                  disabled={isMobileContactVisible}
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
              className={classNames(
                "self-center",
                isMobileContactVisible && "hidden"
              )}
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
        question={t("details.contact_info.delete_dialog.question", {
          email: toBeDeletedEmail,
        })}
        onConfirm={handleDeleteContributor}
        onCancel={() => {
          setToBeDeletedId("");
        }}
      ></QuestionDialog>
    </>
  );
};
