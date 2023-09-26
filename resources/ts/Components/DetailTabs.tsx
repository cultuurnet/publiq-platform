import React from "react";
import { useTranslation } from "react-i18next";
import { classNames } from "../utils/classNames";
import { router } from "@inertiajs/react";

type Props = {
  activeTab: string;
};

export const tabs = [
  "basic_info",
  "integration_info",
  "integration_settings",
  "contact_info",
];

export default function DetailTabs({ activeTab }: Props) {
  const { t } = useTranslation();

  const url = new URL(document.location.href);

  const changeTabInUrl = (tab: string) => {
    url.searchParams.set("tab", tab);
    router.get(url.toString());
  };

  return (
    <ul className="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200">
      {tabs.map((tab) => (
        <li className="mr-2" key={tab}>
          <button
            onClick={() => changeTabInUrl(tab)}
            aria-current="page"
            className={classNames(
              activeTab === tab && "text-publiq-blue-dark bg-gray-100 ",
              "inline-block p-4 rounded-t-lg"
            )}
          >
            {t(`details.${tab}.title`)}
          </button>
        </li>
      ))}
    </ul>
  );
}
