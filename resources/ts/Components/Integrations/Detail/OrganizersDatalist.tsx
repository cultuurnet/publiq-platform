import { Input } from "../../Input";
import type { Props as FormElementProps } from "../../FormElement";
import { FormElement } from "../../FormElement";
import React, { useRef, useState } from "react";
import type { UiTPASOrganizer } from "../../../types/UiTPASOrganizer";
import { debounce } from "lodash";
import { useTranslation } from "react-i18next";
import { Alert } from "../../Alert";
import { ButtonIcon } from "../../ButtonIcon";
import { faTrash } from "@fortawesome/free-solid-svg-icons";

type Props = {
  onChange: (organizers: UiTPASOrganizer[]) => void;
  value: UiTPASOrganizer[];
} & Omit<FormElementProps, "onChange" | "component">;

export const OrganizersDatalist = ({ onChange, value, ...props }: Props) => {
  const { t } = useTranslation();
  const [isSearchListVisible, setIsSearchListVisible] = useState(false);
  const [organizerList, setOrganizerList] = useState<UiTPASOrganizer[]>([]);
  const [organizerError, setOrganizerError] = useState(false);
  const organizersInputRef = useRef<HTMLInputElement>(null);

  const handleGetOrganizers = debounce(
    async (e: React.ChangeEvent<HTMLInputElement>) => {
      const response = await fetch(`/organizers?name=${e.target.value}`);
      try {
        const data = await response.json();
        if (!data || (typeof data === "object" && "exception" in data)) {
          setOrganizerError(true);
          return;
        }

        const organizers = data.map(
          (organizer: { name: string | { nl: string }; id: string }) => {
            if (typeof organizer.name === "object" && "nl" in organizer.name) {
              return { name: organizer.name.nl, id: organizer.id };
            }
            return organizer;
          }
        );
        setOrganizerList(organizers);
      } catch {
        setOrganizerError(true);
        return;
      }

      if (organizerError) {
        setOrganizerError(false);
      }
    },
    750
  );

  const handleInputOnChange = async (
    e: React.ChangeEvent<HTMLInputElement>
  ) => {
    if (e.target.value !== "") {
      await handleGetOrganizers(e);
      setIsSearchListVisible(true);
    } else {
      setIsSearchListVisible(false);
      setOrganizerList([]);
    }
  };

  const handleAddOrganizers = (organizer: UiTPASOrganizer) => {
    const isDuplicate =
      value.length > 0 &&
      value.some((existingOrganizer) => existingOrganizer.id === organizer.id);

    if (!isDuplicate) {
      onChange([...value, organizer]);
      setIsSearchListVisible(false);
      setOrganizerList([]);
      if (organizersInputRef.current) {
        organizersInputRef.current.value = "";
      }
    }
  };

  const handleKeyDown = (
    event: React.KeyboardEvent<HTMLLIElement>,
    organizer: UiTPASOrganizer
  ) => {
    if (event.key === "Enter") {
      handleAddOrganizers(organizer);
    }
  };

  const handleDeleteOrganizer = (deletedOrganizer: string) =>
    onChange(value.filter((organizer) => organizer.name !== deletedOrganizer));

  return (
    <>
      <div className="flex gap-2 flex-wrap">
        {value.length > 0 &&
          value.map((organizer, index) => (
            <div
              key={`${organizer}${index}`}
              className="border rounded px-2 py-1 flex gap-1"
            >
              <p>{organizer.name}</p>
              <ButtonIcon
                icon={faTrash}
                size="sm"
                className="text-icon-gray"
                onClick={() => handleDeleteOrganizer(organizer.name)}
              />
            </div>
          ))}
      </div>
      {organizerError && (
        <Alert variant="error">{t("dialog.invite_error")}</Alert>
      )}
      <FormElement
        label={`${t("details.organizers_info.title")}`}
        required
        className="w-full relative"
        {...props}
        component={
          <div>
            <Input
              type="text"
              name="organizers"
              ref={organizersInputRef}
              onChange={(e) => handleInputOnChange(e)}
            />
            {organizerList &&
              organizerList.length > 0 &&
              isSearchListVisible && (
                <ul className="border rounded absolute bg-white w-full z-50">
                  {organizerList.map((organizer) => (
                    <li
                      tabIndex={0}
                      key={`${organizer.id}`}
                      onClick={() => handleAddOrganizers(organizer)}
                      onKeyDown={(e) => handleKeyDown(e, organizer)}
                      className="border-b px-3 py-1 hover:bg-gray-100"
                    >
                      {organizer.name}
                    </li>
                  ))}
                </ul>
              )}
          </div>
        }
      />
    </>
  );
};
