import React from "react";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { useTranslation } from "react-i18next";
import { classNames } from "../../../utils/classNames";
import { Heading } from "../../Heading";

type Props = {
  name: string;
  description: string;
  website: string;
  errors: Record<string, string | undefined>;
  onChangeName: (val: string) => void;
  onChangeDescription: (val: string) => void;
  onChangeWebsite: (val: string) => void;
};
export const BasicInfo = ({
  name,
  description,
  website,
  errors,
  onChangeName,
  onChangeDescription,
  onChangeWebsite,
}: Props) => {
  const { t } = useTranslation();

  return (
    <div className="max-lg:flex max-lg:flex-col lg:grid lg:grid-cols-3 gap-6 border-b pb-10 border-gray-300">
      <Heading level={4} className="font-medium">
        {t("details.basic_info.title")}
      </Heading>
      <div className="grid-cols-2 flex flex-col gap-5">
        <FormElement
          label={`${t("details.basic_info.name")}`}
          error={errors["integrationName"]}
          component={
            <Input
              type="text"
              name="integrationName"
              value={name}
              onChange={(e) => onChangeName(e.target.value)}
              className="md:min-w-[40rem]"
            />
          }
        />
        <FormElement
          label={`${t("details.basic_info.description")}`}
          error={errors["description"]}
          component={
            <textarea
              rows={4}
              className={classNames(
                "appearance-none rounded-lg block w-full  text-gray-700 border border-gray-500 py-3 px-4 leading-tight md:min-w-[40rem]"
              )}
              name="description"
              value={description}
              onChange={(e) => onChangeDescription(e.target.value)}
            />
          }
        />
        <FormElement
          label={`${t("details.basic_info.website")}`}
          error={errors["website"]}
          component={
            <Input
              type="text"
              name="website"
              value={website}
              onChange={(e) => onChangeWebsite(e.target.value)}
              className="md:min-w-[40rem]"
            />
          }
        />
      </div>
    </div>
  );
};
