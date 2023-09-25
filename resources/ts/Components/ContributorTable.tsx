import React, { ComponentProps } from "react";
import { useTranslation } from "react-i18next";

type Props = ComponentProps<"div">;

export default function ContributorTable({ children }: Props) {
  const { t } = useTranslation();
  return (
    <div className="relative overflow-x-scroll">
      <table className="w-full text-left text-gray-500">
        <thead className="text-gray-700  bg-gray-200 dark:bg-gray-700 ">
          <tr>
            <th scope="col" className="px-6 py-3">
              {t("integration_form.contact.last_name")}
            </th>
            <th scope="col" className="px-6 py-3">
              {t("integration_form.contact.first_name")}
            </th>
            <th scope="col" className="px-6 py-3">
              {t("integration_form.contact.email")}
            </th>
            <th scope="col" className="px-6 py-3">
              {t("integration_form.contact.action")}
            </th>
          </tr>
        </thead>
        <tbody>{children}</tbody>
      </table>
    </div>
  );
}
