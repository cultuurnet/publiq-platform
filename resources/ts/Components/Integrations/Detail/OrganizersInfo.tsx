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
import { t } from "i18next";
import { QuestionDialog } from "../../QuestionDialog";
import { Dialog } from "../../Dialog";
import { ButtonSecondary } from "../../ButtonSecondary";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { Datalist } from "../../Datalist";
import { ActivationDialog } from "../../ActivationDialog";
import { router } from "@inertiajs/react";
import { OrganizersDatalist } from "./OrganizersDatalist";

type Props = Integration & { organizers: Organizer[] };

const OrganizersSection = ({
  sectionName,
  organizers,
}: {
  organizers: Organizer[];
  sectionName: Organizer["status"];
}) => {
  const { t, i18n } = useTranslation();
  const [isModalVisible, setIsModalVisible] = useState(true);
  const searchResults = [
    { id: "foo", name: "foo" },
    { id: "bar", name: "bar" },
  ];
  if (!organizers?.length) {
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
              <CopyText text={organizer.id} />
            </div>
            {sectionName === "Live" && (
              <div>
                <ButtonIcon icon={faPencil} className="text-icon-gray" />
                <ButtonIcon icon={faTrash} className="text-icon-gray" />
              </div>
            )}
          </div>
        </Card>
      ))}
      <div className="grid lg:grid-cols-3">
        {sectionName === "Live" && (
          <ButtonPrimary
            className="col-span-1"
            onClick={() => setIsModalVisible(true)}
          >
            {t("details.organizers_info.add")}
          </ButtonPrimary>
        )}
      </div>
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
            <ButtonPrimary
              onClick={() => {
                alert("lol");
              }}
            >
              {t("dialog.confirm")}
            </ButtonPrimary>
          </>
        }
      >
        <Heading level={5} className="font-light">
          Geef de UiTdatabank-organisaties op waarvoor je acties in UiTPAS wilt
          uitvoeren.
        </Heading>
        <OrganizersDatalist onSelect={alert} />
      </Dialog>
    </>
  );
};

export const OrganizersInfo = ({ organizers }: Props) => {
  const { t } = useTranslation();
  const byStatus = groupBy(organizers, "status");

  return (
    <>
      <Heading level={4} className="font-semibold">
        {t("details.organizers_info.title")}
      </Heading>
      <p>{t("details.organizers_info.description")}</p>
      <OrganizersSection sectionName="Test" organizers={byStatus["Test"]} />
      <OrganizersSection sectionName="Live" organizers={byStatus["Live"]} />
    </>
  );
};
