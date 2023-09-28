import React, { useState } from "react";
import { ButtonSecondary } from "../../ButtonSecondary";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faTrash } from "@fortawesome/free-solid-svg-icons";
import { QuestionDialog } from "../../QuestionDialog";
import { router } from "@inertiajs/react";
import { t } from "i18next";
import { Integration } from "../../../Pages/Integrations/Index";
import { Heading } from "../../Heading";

type Props = Integration;

export const MenageIntegration = ({ id }: Props) => {
  const [isModalVisible, setIsModalVisible] = useState(false);
  const handleDeleteIntegration = () => {
    router.delete(`/integrations/${id}`, {});
  };
  return (
    <>
      <div className="flex flex-col gap-4 max-md:px-5 px-10 py-5">
        <div className="max-w-[30rem] flex flex-col gap-5">
          <Heading level={2} className="font-semibold">
            {t("details.menage_account.delete.title")}
          </Heading>
          <p>{t("details.menage_account.delete.description")}</p>
          <ButtonSecondary
            className="self-start"
            variant="danger"
            onClick={() => setIsModalVisible(true)}
          >
            {t("details.menage_account.action")}
            <FontAwesomeIcon className="pl-1" icon={faTrash} />
          </ButtonSecondary>
        </div>
        <QuestionDialog
          isVisible={isModalVisible}
          onClose={() => {
            setIsModalVisible(false);
          }}
          question={t("integrations.dialog.delete")}
          onConfirm={handleDeleteIntegration}
          onCancel={() => {
            setIsModalVisible(false);
          }}
        ></QuestionDialog>
      </div>
    </>
  );
};
