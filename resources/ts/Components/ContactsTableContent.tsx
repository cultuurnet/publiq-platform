import React, { ComponentProps } from "react";
import { ContactsTableProps } from "./ContactsTable";
import { classNames } from "../utils/classNames";
import { ButtonIcon } from "./ButtonIcon";
import { faPencil, faTrash } from "@fortawesome/free-solid-svg-icons";
import { useTranslation } from "react-i18next";

type Props = { desktop?: boolean; mobile?: boolean } & ContactsTableProps &
  ComponentProps<"div">;

export const ContactsTableContent = ({
  desktop,
  mobile,
  data,
  onEdit,
  onDelete,
  onPreview,
  functionalId,
  technicalId,
}: Props) => {
  const { t } = useTranslation();
  const contactTypes = [
    { label: "functional", id: functionalId },
    { label: "technical", id: technicalId },
  ] as const;

  return (
    <tbody>
      {contactTypes.map((type) => (
        <tr
          className={classNames(
            "bg-white border-b",
            desktop && "hover:bg-publiq-gray-50"
          )}
          key={type.id}
        >
          <td
            className={classNames(
              desktop && "w-2/4",
              mobile && "px-2 py-4 w-full"
            )}
            onClick={
              mobile
                ? () => {
                    onPreview(true);
                    onEdit(type.id);
                  }
                : undefined
            }
          >
            <div
              className={classNames(
                desktop && "px-6 pt-4 font-semibold",
                mobile &&
                  "text-publiq-blue-dark flex gap-2 items-center underline"
              )}
            >
              {data[type.label]?.firstName} {data[type.label].lastName}
            </div>
            <div
              className={classNames(
                desktop && "px-6 pb-4",
                mobile && "text-xs"
              )}
            >
              {t(`integration_form.contact_label_${type.label}`)}
            </div>
          </td>
          {desktop && (
            <td className="w-2/4 px-6 py-4">{data[type.label].email}</td>
          )}
          <td
            className={classNames(
              desktop && "px-6",
              mobile && "px-3 py-4 flex justify-start"
            )}
          >
            <ButtonIcon
              icon={faPencil}
              className="text-icon-gray"
              onClick={() => onEdit(type.id)}
            />
          </td>
        </tr>
      ))}
      {data.contributors.map((contributor) => (
        <tr
          key={contributor.id}
          className={classNames(
            "bg-white border-b",
            desktop && "hover:bg-publiq-gray-50"
          )}
        >
          <td
            className={classNames(mobile && "px-2 py-4")}
            onClick={
              mobile
                ? () => {
                    onPreview(true);
                    onEdit(contributor.id);
                  }
                : undefined
            }
          >
            <div
              className={classNames(
                desktop && "px-6 font-semibold",
                mobile && "text-publiq-blue-dark underline"
              )}
            >
              {contributor.firstName} {contributor.lastName}
            </div>
            <div className={classNames(desktop && "px-6", mobile && "text-xs")}>
              {t("integration_form.contact_label_contributor")}
            </div>
          </td>
          {desktop && <td className="px-6 py-4">{contributor.email}</td>}
          <td
            className={classNames(
              desktop && "px-6 py-4 flex",
              mobile && "px-2 py-4 flex items-center"
            )}
          >
            <ButtonIcon
              icon={faPencil}
              className="text-icon-gray"
              onClick={() => onEdit(contributor.id)}
            />
            <ButtonIcon
              icon={faTrash}
              className="text-icon-gray"
              onClick={() => onDelete(contributor.id, contributor.email)}
            />
          </td>
        </tr>
      ))}
    </tbody>
  );
};
