import React, { useState } from "react";
import { useTranslation } from "react-i18next";
import { Dialog } from "./Dialog";
import { ButtonSecondary } from "./ButtonSecondary";
import { ButtonPrimary } from "./ButtonPrimary";
import { useIsMobile } from "../hooks/useIsMobile";
import { FormElement } from "./FormElement";
import { Input } from "./Input";
import { Alert } from "./Alert";
import { useForm } from "@inertiajs/react";

type Props = {
  isVisible?: boolean;
  onClose: () => void;
};

export const NewsletterDialog = ({ isVisible, onClose }: Props) => {
  const { t } = useTranslation();

  const isMobile = useIsMobile();

  const [isFormVisible, setIsFormVisible] = useState(true);

  const initialFormValues = {
    email: "",
  };

  const { data, setData, post, errors: err } = useForm(initialFormValues);

  const handleSubmit = () => {
    post("/newsletter", {
      onSuccess: () => setIsFormVisible(false),
    });
  };

  const errors = err as Record<string, string | undefined>;

  return (
    <Dialog
      title={t("footer.newsletter_dialog.title")}
      actions={
        isFormVisible && (
          <>
            <ButtonSecondary>{t("dialog.cancel")}</ButtonSecondary>
            <ButtonPrimary onClick={handleSubmit}>
              {t("dialog.confirm")}
            </ButtonPrimary>
          </>
        )
      }
      isFullscreen={isMobile}
      isVisible={isVisible}
      onClose={onClose}
    >
      {isFormVisible ? (
        <FormElement
          label={`${t("footer.newsletter_dialog.email")}`}
          className="col-span-2"
          error={errors["email"] || errors["mailjet"]}
          component={
            <Input
              type="text"
              name="email"
              placeholder={t("footer.newsletter_dialog.placeholder")}
              value={data.email}
              onChange={(e) => setData("email", e.target.value)}
            />
          }
        />
      ) : (
        <Alert
          variant="success"
          title={t("footer.newsletter_dialog.success.title")}
        >
          <p>{t("footer.newsletter_dialog.success.description")}</p>
        </Alert>
      )}
    </Dialog>
  );
};
