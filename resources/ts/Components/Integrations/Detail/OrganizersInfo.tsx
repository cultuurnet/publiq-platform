import React, { useState } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import type { Integration } from "../../../types/Integration";
import { Card } from "../../Card";
import { CopyText } from "../../CopyText";
import { ButtonIcon } from "../../ButtonIcon";
import { faPencil, faTrash } from "@fortawesome/free-solid-svg-icons";
import type { Organizer } from "../../../types/Organizer";
import { groupBy } from "lodash";
import { ButtonPrimary } from "../../ButtonPrimary";
import { QuestionDialog } from "../../QuestionDialog";
import { router } from "@inertiajs/react";

type Props = Integration & { organizers: Organizer[] };

const OrganizersSection = ({
  id,
  sectionName,
  organizers,
}: {
  id: string;
  organizers: Organizer[];
  sectionName: Organizer["status"];
}) => {
  const { t, i18n } = useTranslation();
  const [toBeDeletedId, setToBeDeletedId] = useState("");

  const handleDeleteOrganizer = () => {
    router.delete(`/integrations/${id}/organizers/${toBeDeletedId}`, {
      preserveScroll: true,
      preserveState: false,
    });
  };

  if (!organizers.length) {
    return null;
  }

  return (
    <>
      <Heading level={4} className="font-semibold">
        {sectionName}
      </Heading>
      {organizers.map((organizer) => (
        <Card key={organizer.id}>
          <div className="grid grid-cols-[1fr,2fr,auto] gap-x-4 items-center">
            <h1 className={"font-bold"}>{organizer.name[i18n.language]}</h1>
            <div>
              <CopyText>{organizer.id}</CopyText>
            </div>
            {sectionName === "Live" && (
              <div>
                <ButtonIcon icon={faPencil} className="text-icon-gray" />
                <ButtonIcon
                  icon={faTrash}
                  className="text-icon-gray"
                  onClick={() => setToBeDeletedId(organizer.id)}
                />
              </div>
            )}
          </div>
        </Card>
      ))}
      <div className="grid lg:grid-cols-3">
        {sectionName === "Live" && (
          <ButtonPrimary className="col-span-1">
            {t("details.organizers_info.add")}
          </ButtonPrimary>
        )}
      </div>
      <QuestionDialog
        isVisible={!!toBeDeletedId}
        onClose={() => {
          setToBeDeletedId("");
        }}
        title={t("details.organizers_info.delete_dialog.title")}
        question={t("details.organizers_info.delete_dialog.question", {
          name: organizers.find((organizer) => organizer.id === toBeDeletedId)
            ?.name[i18n.language],
        })}
        onConfirm={handleDeleteOrganizer}
        onCancel={() => setToBeDeletedId("")}
      />
    </>
  );
};

export const OrganizersInfo = ({ id, organizers }: Props) => {
  const { t } = useTranslation();
  const byStatus = groupBy(organizers, "status");

  return (
    <>
      <Heading level={4} className="font-semibold">
        {t("details.organizers_info.title")}
      </Heading>
      <p>{t("details.organizers_info.description")}</p>
      <OrganizersSection
        id={id}
        sectionName="Test"
        organizers={byStatus["Test"]}
      />
      <OrganizersSection
        id={id}
        sectionName="Live"
        organizers={byStatus["Live"]}
      />
    </>
  );
};
