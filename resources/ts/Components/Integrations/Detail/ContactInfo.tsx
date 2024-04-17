import React, { useEffect, useMemo, useState } from "react";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { useTranslation } from "react-i18next";
import { ButtonPrimary } from "../../ButtonPrimary";
import { router, useForm } from "@inertiajs/react";
import { ContactType } from "../../../types/ContactType";
import { ButtonSecondary } from "../../ButtonSecondary";
import { QuestionDialog } from "../../QuestionDialog";
import { Dialog } from "../../Dialog";
import { ContactsTable } from "../../ContactsTable";
import { classNames } from "../../../utils/classNames";
import { Heading } from "../../Heading";
import type { Contact } from "../../../types/Contact";
import type { Integration } from "../../../types/Integration";
import { Alert } from "../../Alert";

export type ContactFormData = {
  functional: Contact;
  technical: Contact;
  contributors: Contact[];
};

type Props = {
  isMobile: boolean;
} & Integration;

export const ContactInfo = ({ id, contacts, isMobile }: Props) => {
  const { t } = useTranslation();
  const [isDisabled, setIsDisabled] = useState(true);
  const [isAddFormVisible, setIsAddFormVisible] = useState(false);
  const [toBeDeletedId, setToBeDeletedId] = useState("");
  const [toBeDeletedEmail, setToBeDeletedEmail] = useState("");
  const [toBeEditedId, setToBeEditedId] = useState("");
  const [isMobileContactVisible, setIsMobileContactVisible] = useState(false);
  const [isContactInfoAlertVisible, setIsContactInfoAlertVisible] =
    useState(true);

  const functionalContact = useMemo(
    // We know for sure there is a functional contact
    () => contacts.find((contact) => contact.type === ContactType.Functional)!,
    [contacts]
  );

  const technicalContact = useMemo(
    // We know for sure there is a technical contact
    () => contacts.find((contact) => contact.type === ContactType.Technical)!,
    [contacts]
  );

  const contributorContacts = useMemo(
    () =>
      contacts.filter((contact) => contact.type === ContactType.Contributor),
    [contacts]
  );
  
  const storeContactForm = useForm({ email: "", firstName: "", lastName: "" });

  const createInitialFormData = () => {
    return {
      functional: { ...functionalContact, changed: false },
      technical: { ...technicalContact, changed: false },
      contributors: contributorContacts.map((contributor) => ({
        ...contributor,
        changed: false,
      })),
    };
  };

  const updateContactForm = useForm(createInitialFormData());

  useEffect(() => {
    updateContactForm.setData(createInitialFormData());

    // form is not a stable reference and triggers whenever a field value changes
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [contributorContacts, toBeEditedId]);

  updateContactForm.transform(
    (data) =>
      ({
        ...data,
        functional: data.functional.changed ? data.functional : undefined,
        technical: data.technical.changed ? data.technical : undefined,
        contributors: data.contributors.filter((c) => c.changed),
      }) as typeof data
  );

  const errors = updateContactForm.errors as Record<string, string | undefined>;

  const changeContact = (type: ContactType, newData: Contact) => {
    const property = type === "contributor" ? "contributors" : type;

    updateContactForm.setData((prevData) => ({
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
      preserveState: false,
    });
  };

  const handleSaveChanges = () => {
    setIsDisabled(true);
    storeContactForm.post(`/integrations/${id}/contacts`, {
      preserveScroll: true,
      onSuccess: () => {
        setIsAddFormVisible(false);
      },
    });
  };

  const foundContributor = useMemo(
    () =>
      updateContactForm.data.contributors.find(
        (contributor) => contributor.id === toBeEditedId
      ),
    [updateContactForm.data.contributors, toBeEditedId]
  );

  const [formContactType, setFormContactType] = useState("" as ContactType);

  const toBeEditedContact = useMemo(() => {
    if (functionalContact.id === toBeEditedId) {
      setFormContactType(ContactType.Functional);
      return updateContactForm.data.functional;
    }
    if (technicalContact.id === toBeEditedId) {
      setFormContactType(ContactType.Technical);
      return updateContactForm.data.technical;
    }
    if (foundContributor) {
      setFormContactType(ContactType.Contributor);
      return foundContributor;
    }
  }, [
    functionalContact,
    technicalContact,
    foundContributor,
    updateContactForm.data.functional,
    updateContactForm.data.technical,
    toBeEditedId,
  ]);

  return (
    <>
      <div className="w-full flex flex-col gap-6">
        <Heading level={4} className="font-semibold col-span-1">
          {t("details.contact_info.title")}
        </Heading>
        {isContactInfoAlertVisible && (
          <Alert
            variant="error"
            title={t("details.contact_info.alert.title")}
            closable
            onClose={() => setIsContactInfoAlertVisible(false)}
          >
            {t("details.contact_info.alert.description")}
          </Alert>
        )}
        <p>{t("integration_form.contact.description")}</p>
        <ContactsTable
          data={{
            functional: functionalContact,
            technical: technicalContact,
            contributors: contributorContacts,
          }}
          onEdit={(id) => setToBeEditedId(id)}
          onDelete={(id, email) => {
            setToBeDeletedId(id);
            setToBeDeletedEmail(email);
          }}
          onPreview={(bool) => setIsMobileContactVisible(bool)}
          functionalId={functionalContact.id}
          technicalId={technicalContact.id}
          className="col-span-2"
        />
      </div>

      {!isAddFormVisible && (
        <ButtonPrimary
          onClick={() => {
            setIsAddFormVisible(true);
            setIsDisabled(true);
          }}
          className="self-start"
        >
          {t("integration_form.add")}
        </ButtonPrimary>
      )}

      <Dialog
        isVisible={isAddFormVisible}
        onClose={() => setIsAddFormVisible(false)}
        isFullscreen={isMobile}
        contentStyles="gap-3"
        title={t("details.contact_info.new")}
        actions={
          <>
            <ButtonSecondary onClick={() => setIsAddFormVisible(false)}>
              {t("dialog.cancel")}
            </ButtonSecondary>
            <ButtonPrimary
              onClick={() => {
                handleSaveChanges();
              }}
              className="p-0"
            >
              {t("dialog.confirm")}
            </ButtonPrimary>
          </>
        }
      >
        <div
          className={classNames(
            "w-full",
            !isMobile && "grid grid-cols-2 gap-3"
          )}
        >
          <FormElement
            label={`${t("integration_form.contact.last_name")}`}
            error={storeContactForm.errors["lastName"]}
            className="w-full"
            component={
              <Input
                type="text"
                name="lastName"
                value={storeContactForm.data.lastName}
                onChange={(e) =>
                  storeContactForm.setData("lastName", e.target.value)
                }
                className="w-full"
              />
            }
          />
          <FormElement
            label={`${t("integration_form.contact.first_name")}`}
            error={storeContactForm.errors["firstName"]}
            className="w-full"
            component={
              <Input
                type="text"
                name="firstName"
                value={storeContactForm.data.firstName}
                onChange={(e) =>
                  storeContactForm.setData("firstName", e.target.value)
                }
              />
            }
          />
        </div>
        <FormElement
          label={`${t("integration_form.contact.email")}`}
          error={storeContactForm.errors["email"]}
          component={
            <Input
              type="text"
              name="email"
              value={storeContactForm.data.email}
              onChange={(e) =>
                storeContactForm.setData("email", e.target.value)
              }
            />
          }
        />
      </Dialog>
      {toBeEditedContact && (
        <Dialog
          title={t("details.contact_info.edit_dialog")}
          actions={
            !isMobileContactVisible && (
              <>
                <ButtonSecondary onClick={() => setToBeEditedId("")}>
                  {t("dialog.cancel")}
                </ButtonSecondary>
                <ButtonPrimary
                  onClick={() => {
                    updateContactForm.patch(`/integrations/${id}/contacts`, {
                      preserveScroll: true,
                      onSuccess: () => {
                        setToBeEditedId("");
                      },
                    });
                  }}
                >
                  {t("dialog.confirm")}
                </ButtonPrimary>
              </>
            )
          }
          isVisible={!!toBeEditedId}
          onClose={() => {
            setToBeEditedId("");
            setIsMobileContactVisible(false);
          }}
          isFullscreen={isMobile}
          contentStyles="gap-3"
        >
          <div
            className={classNames(
              "w-full",
              !isMobile && "grid grid-cols-2 gap-3"
            )}
          >
            <FormElement
              label={`${t("integration_form.contact.last_name")}`}
              error={errors[`${formContactType}.lastName`]}
              className="w-full"
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
              className="w-full"
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
          </div>
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
        </Dialog>
      )}

      {!isDisabled && (
        <div className="flex flex-col gap-2 items-center">
          <ButtonPrimary
            onClick={() => {
              setIsDisabled(true);

              updateContactForm.patch(`/integrations/${id}/contacts`, {
                preserveScroll: true,
              });
            }}
          >
            {t("details.save")}
          </ButtonPrimary>
        </div>
      )}
      <QuestionDialog
        isVisible={!!toBeDeletedId}
        onClose={() => {
          setToBeDeletedId("");
        }}
        title={t("details.contact_info.delete_dialog.title")}
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
