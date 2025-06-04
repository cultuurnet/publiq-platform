import React, { useState } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import type { Integration } from "../../../types/Integration";
import { Card } from "../../Card";
import { CopyText } from "../../CopyText";
import { ButtonIcon } from "../../ButtonIcon";
import { faTrash } from "@fortawesome/free-solid-svg-icons";
import type { Organizer } from "../../../types/Organizer";
import { groupBy } from "lodash";
import { ButtonPrimary } from "../../ButtonPrimary";
import { QuestionDialog } from "../../QuestionDialog";
import { router, useForm } from "@inertiajs/react";
import { Dialog } from "../../Dialog";
import { ButtonSecondary } from "../../ButtonSecondary";
import { OrganizersDatalist } from "./OrganizersDatalist";
import type { UiTPASOrganizer } from "../../../types/UiTPASOrganizer";
import { classNames } from "../../../utils/classNames";

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
  const [isModalVisible, setIsModalVisible] = useState(false);
  const form = useForm<{ organizers: UiTPASOrganizer[] }>({
    organizers: [],
  });

  const handleDeleteOrganizer = () => {
    router.delete(`/integrations/${id}/organizers/${toBeDeletedId}`, {
      preserveScroll: true,
      preserveState: false,
    });
  };

  const handleUpdateOrganizers = () =>
    router.post(`/integrations/${id}/organizers`, form.data, {
      preserveScroll: false,
      preserveState: false,
    });

  if (!organizers?.length) {
    return null;
  }

  return (
    <>
      <Heading level={4} className="font-semibold">
        {sectionName}
      </Heading>
      <div className="gap-0">
        {organizers.map((organizer, index) => (
            <Card
                key={organizer.id}
                className={`m-0 drop-shadow-none border border-gray-200 border-t-0 first:border-t z-[${organizers.length - index}]`}
            >
                <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-2 md:gap-4">
                    <Heading level={5} className="font-semibold text-publiq-gray-600">
                        {organizer.name[i18n.language]}
                    </Heading>
                    <div className="flex-shrink-0 flex max-sm:flex-col gap-4">
                        <CopyText text={organizer.id}/>
                        <ButtonIcon
                            data-testid={organizer.name[i18n.language]}
                            icon={faTrash}
                            className={classNames(
                                sectionName !== "Live" && "invisible max-sm:hidden",
                                "text-icon-gray"
                            )}
                            onClick={() => setToBeDeletedId(organizer.id)}
                        />
                    </div>
                </div>

                <div className="mt-2 ml-1">
                    {Array.isArray(organizer.permissions) && organizer.permissions.length > 0 ? (
                        <ul className="flex flex-col gap-1 text-sm text-gray-700">
                            {organizer.permissions.map((permission: string, id: number) => (
                                <li key={id} className="flex items-start gap-2">
                                    <span className="text-green-600 mt-0.5">✅</span>
                                    <span>{permission}</span>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-sm italic text-gray-400">
                            {t("details.organizers_info.no_permissions")}
                        </p>
                    )}
                </div>
            </Card>
        ))}
      </div>
        {sectionName === "Live" && (
            <ButtonPrimary
                className="self-start"
                onClick={() => setIsModalVisible(true)}
            >
                {t("details.organizers_info.add")}
            </ButtonPrimary>
        )}
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
        <Dialog
            isVisible={isModalVisible}
            onClose={() => setIsModalVisible(false)}
            title={t("details.organizers_info.add")}
        contentStyles="gap-3"
        actions={
          <>
            <ButtonSecondary onClick={() => setIsModalVisible(false)}>
              {t("dialog.cancel")}
            </ButtonSecondary>
            <ButtonPrimary onClick={handleUpdateOrganizers}>
              {t("dialog.confirm")}
            </ButtonPrimary>
          </>
        }
      >
        <Heading level={5} className="font-light">
          {t("details.organizers_info.update_dialog.question")}
        </Heading>
        <OrganizersDatalist
          onChange={(organizers) => form.setData("organizers", organizers)}
          value={form.data.organizers}
        />
      </Dialog>
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
