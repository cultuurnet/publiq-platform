import { Input } from "../../Input";
import { FormElement, Props as FormElementProps } from "../../FormElement";
import React, { useRef, useState } from "react";
import type { UiTPASOrganizer } from "../../../types/UiTPASOrganizer";
import { debounce } from "lodash";
import { useTranslation } from "react-i18next";
import { Alert } from "../../Alert";

export const OrganizersDatalist = ({
  onSelect,
  ...props
}: Omit<FormElementProps, "component">) => {
  const { t } = useTranslation();
  const [isSearchListVisible, setIsSearchListVisible] = useState(false);
  const [organizerList, setOrganizerList] = useState<UiTPASOrganizer[]>([]);
  const [organizerError, setOrganizerError] = useState(false);
  const organizersInputRef = useRef<HTMLInputElement>(null);

  const handleGetOrganizers = debounce(
    async (e: React.ChangeEvent<HTMLInputElement>) => {
      const response = await fetch(`/organizers?name=${e.target.value}`);
      const data = await response.json();
      if ("exception" in data) {
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

  return (
    <>
      {organizerError && (
        <Alert variant="error">{t("dialog.invite_error")}</Alert>
      )}
      <FormElement
        label={`${t("details.organizers_info.title")}`}
        required
        className="w-full relative"
        {...props}
        component={
          <>
            <Input
              type="text"
              name="organizers"
              ref={organizersInputRef}
              onChange={async (e) => {
                await handleInputOnChange(e);
              }}
            />
            {organizerList &&
              organizerList.length > 0 &&
              isSearchListVisible && (
                <ul className="border rounded absolute bg-white w-full z-50">
                  {organizerList.map((organizer) => (
                    <li
                      tabIndex={0}
                      key={`${organizer.id}`}
                      onClick={() => onSelect(organizer)}
                      onKeyDown={(e) => handleKeyDown(e, organizer)}
                      className="border-b px-3 py-1 hover:bg-gray-100"
                    >
                      {organizer.name}
                    </li>
                  ))}
                </ul>
              )}
          </>
        }
      />
    </>
  );
};
