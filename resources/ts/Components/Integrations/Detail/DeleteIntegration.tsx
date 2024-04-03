import React, { useState } from "react";
import { ButtonSecondary } from "../../ButtonSecondary";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faTrash } from "@fortawesome/free-solid-svg-icons";
import { QuestionDialog } from "../../QuestionDialog";
import { router } from "@inertiajs/react";
import { t } from "i18next";
import { Heading } from "../../Heading";
import { Integration } from "../../../types/Integration";

type Props = Integration;

export const DeleteIntegration = ({ id }: Props) => {
  const [isModalVisible, setIsModalVisible] = useState(false);
  const handleDeleteIntegration = () => {
    router.delete(`/integrations/${id}`, {});
  };
  return (
    <>
      <div className="flex flex-col gap-5">
        <Heading level={4} className="font-semibold">
          {t("details.delete_integration.title")}
        </Heading>
        <p>{t("details.delete_integration.delete.description.part2")}</p>
        <p> {t("details.delete_integration.delete.description.part3")}</p>
        <ButtonSecondary
          className="self-start"
          variant="danger"
          onClick={() => setIsModalVisible(true)}
        >
          {t("details.delete_integration.delete.title")}
          <FontAwesomeIcon className="pl-1" icon={faTrash} />
        </ButtonSecondary>
      </div>
      <QuestionDialog
        isVisible={isModalVisible}
        onClose={() => {
          setIsModalVisible(false);
        }}
        title={t("details.delete_integration.delete.title")}
        question={t("details.delete_integration.delete.question")}
        onConfirm={handleDeleteIntegration}
        onCancel={() => {
          setIsModalVisible(false);
        }}
      />
    </>
  );
};
