import React from "react";
import { useTranslation } from "react-i18next";
import { classNames } from "../utils/classNames";

type Props = {
  isVisible: string;
  onNavigation: (component: string) => void;
};

export default function DetailTabs({ isVisible, onNavigation }: Props) {
  const { t } = useTranslation();

  return (
    <ul className="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200">
      <li className="mr-2">
        <button
          onClick={() => onNavigation("basic-info")}
          aria-current="page"
          className={classNames(
            isVisible === "basic-info" && "text-publiq-blue-dark bg-gray-100 ",
            "inline-block p-4 rounded-t-lg"
          )}
        >
          {t("details.basic_info.title")}
        </button>
      </li>
      <li className="mr-2">
        <button
          onClick={() => onNavigation("integration-info")}
          className={classNames(
            isVisible === "integration-info" &&
              "text-publiq-blue-dark bg-gray-100 ",
            "inline-block p-4 rounded-t-lg"
          )}
        >
          {t("details.integration_info.title")}
        </button>
      </li>
      <li className="mr-2">
        <button
          onClick={() => onNavigation("integration-settings")}
          className={classNames(
            isVisible === "integration-settings" &&
              "text-publiq-blue-dark bg-gray-100 ",
            "inline-block p-4 rounded-t-lg"
          )}
        >
          {t("details.integration_settings.title")}
        </button>
      </li>
      <li className="mr-2">
        <button
          onClick={() => onNavigation("contact-info")}
          className={classNames(
            isVisible === "contact-info" &&
              "text-publiq-blue-dark bg-gray-100 ",
            "inline-block p-4 rounded-t-lg"
          )}
        >
          {t("details.contact_info.title")}
        </button>
      </li>
      <li>
        <button
          onClick={() => onNavigation("billing-info")}
          className={classNames(
            isVisible === "billing-info" &&
              "text-publiq-blue-dark bg-gray-100 ",
            "inline-block p-4 rounded-t-lg"
          )}
        >
          {t("details.billing_info.title")}
        </button>
      </li>
    </ul>
  );
}
