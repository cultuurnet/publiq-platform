import React from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import type { Integration } from "../../../types/Integration";
import { Card } from "../../Card";
import { CopyText } from "../../CopyText";
import { ButtonIcon } from "../../ButtonIcon";
import { faPencil, faTrash } from "@fortawesome/free-solid-svg-icons";
import { Organizer } from "../../../types/Organizer";
import { groupBy } from "lodash";
import { ButtonPrimary } from "../../ButtonPrimary";

type Props = Integration & { organizers: Organizer[] };

const OrganizersSection = ({
  sectionName,
  organizers,
}: {
  organizers: Organizer[];
  sectionName: Organizer["status"];
}) => {
  const { t, i18n } = useTranslation();
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
                <ButtonIcon icon={faTrash} className="text-icon-gray" />
              </div>
            )}
          </div>
        </Card>
      ))}
      {sectionName === "Live" && (
        <ButtonPrimary>Organisatie toevoegen</ButtonPrimary>
      )}
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
