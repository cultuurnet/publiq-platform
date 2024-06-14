import React from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import type { Integration } from "../../../types/Integration";
import { Card } from "../../Card";
import { CopyText } from "../../CopyText";
import { ButtonIcon } from "../../ButtonIcon";
import { faPencil, faTrash } from "@fortawesome/free-solid-svg-icons";
import { Organizer } from "../../../types/Organizer";

type Props = Integration & { organizers: Organizer[] };

export const OrganizersInfo = ({ organizers }: Props) => {
  const { t, i18n } = useTranslation();

  return (
    <>
      <Heading level={4} className="font-semibold">
        {t("details.organizers_info.title")}
      </Heading>
      <p>
        Hieronder vind je een overzicht van de UiTdatabank organisaties waarvoor
        je acties kan uitvoeren in de UiTPAS API.
      </p>
      {organizers.map((organizer) => (
        <Card key={organizer.id}>
          <div className="grid grid-cols-[1fr,2fr,auto] gap-x-4 items-center">
            <h1 className={"font-bold"}>{organizer.name[i18n.language]}</h1>
            <div>
              <CopyText>{organizer.id}</CopyText>
            </div>
            <div>
              <ButtonIcon icon={faPencil} className="text-icon-gray" />
              <ButtonIcon icon={faTrash} className="text-icon-gray" />
            </div>
          </div>
        </Card>
      ))}
    </>
  );
};
