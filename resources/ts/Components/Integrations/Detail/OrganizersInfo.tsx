import React from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import type { Integration } from "../../../types/Integration";
import { Card } from "../../Card";
import { CopyText } from "../../CopyText";
import { ButtonIcon } from "../../ButtonIcon";
import { faPencil, faTrash } from "@fortawesome/free-solid-svg-icons";

type Props = Integration;

export const OrganizersInfo = (props: Props) => {
  const { t, i18n } = useTranslation();

  console.log(props);
  return (
    <>
      <Heading level={4} className="font-semibold">
        {t("details.organizers_info.title")}
      </Heading>
      <p>
        Hieronder vind je een overzicht van de UiTdatabank organisaties waarvoor
        je acties kan uitvoeren in de UiTPAS API.
      </p>

      {props.organizers.map((organizer) => (
        <Card>
          <div className="grid grid-cols-3 gap-4">
            <h1 className={"font-bold"}>{organizer.name[i18n.language]}</h1>
            <CopyText>{organizer.id}</CopyText>
            <div>
              <ButtonIcon icon={faPencil} className="text-icon-gray" />
              <ButtonIcon icon={faTrash} className="text-icon-gray" />
            </div>
            <div className="font-bold">Permissies</div>
            <div>
              <ul className="list-disc list-inside space-y-1 text-gray-700">
                <li>Tarieven opvragen</li>
                <li>UITPAS prijzen ophalen</li>
                <li>Organisatoren zoeken</li>
                <li>Voordelen zoeken</li>
                <li>Voordelen aanmaken en aanpassen</li>
              </ul>
            </div>
          </div>
        </Card>
      ))}
    </>
  );
};
