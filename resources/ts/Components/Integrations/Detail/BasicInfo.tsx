import React, { useState } from "react";
import { Heading } from "../../Heading";
import { ButtonIcon } from "../../ButtonIcon";
import { faPencil } from "@fortawesome/free-solid-svg-icons";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { useTranslation } from "react-i18next";
import { Integration } from "../../../Pages/Integrations/Index";
import { Button } from "../../Button";
import { classNames } from "../../../utils/classNames";
import { useForm } from "@inertiajs/react";

type Props = {
  integration: Integration;
  isMobile?: boolean;
};
export const BasicInfo = ({ integration, isMobile }: Props) => {
  const { t } = useTranslation();
  const [isDisabled, setIsDisabled] = useState(true);

  const initialFormValues = {
    integrationName: integration.name,
    description: integration.description,
  };

  const { data, setData, patch } = useForm(initialFormValues);

  return (
    <div className="flex flex-col gap-4 shadow-md shadow-slate-200 max-md:px-5 px-10 py-5">
      <div className="flex gap-2 items-center">
        <Heading level={2} className="font-semibold">
          {t("details.basic_info.title")}
        </Heading>
        <ButtonIcon
          icon={faPencil}
          className="text-icon-gray"
          onClick={() => setIsDisabled((prev) => !prev)}
        />
      </div>
      <div className="flex flex-col gap-6 border-t py-6">
        <FormElement
          label={`${t("details.basic_info.name")}`}
          labelPosition={isMobile ? "top" : "left"}
          component={
            <Input
              type="text"
              name="integrationName"
              value={data.integrationName}
              onChange={(e) => setData("integrationName", e.target.value)}
              className="md:min-w-[32rem]"
              disabled={isDisabled}
            />
          }
        />
        <FormElement
          label={`${t("details.basic_info.description")}`}
          labelPosition={isMobile ? "top" : "left"}
          component={
            <textarea
              rows={4}
              className={classNames(
                "appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 py-3 px-4 leading-tight md:min-w-[32rem]",
                !isDisabled && "outline-none bg-white border-gray-500"
              )}
              name="description"
              value={data.description}
              onChange={(e) => setData("description", e.target.value)}
              disabled={isDisabled}
            />
          }
        />
        {!isDisabled && (
          <div className="flex flex-col items-start md:pl-[10.5rem]">
            <Button
              onClick={() => {
                setIsDisabled(true);

                patch(`/integrations/${integration.id}`, {
                  preserveScroll: true,
                });
              }}
            >
              {t("details.save")}
            </Button>
          </div>
        )}
      </div>
    </div>
  );
};
