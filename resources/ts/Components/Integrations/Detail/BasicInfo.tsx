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

type Props = {
  integration: Integration;
  isMobile?: boolean;
};
export const BasicInfo = ({ integration, isMobile }: Props) => {
  const { t } = useTranslation();
  const [isDisabled, setIsDisabled] = useState(true);

  return (
    <div className="flex flex-col gap-4">
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
              name="organisationFunctionalContact"
              defaultValue={integration.name}
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
              defaultValue={integration.description}
              disabled={isDisabled}
            />
          }
        />
        <div className="flex flex-col items-start md:pl-[10.5rem]">
          <Button onClick={() => setIsDisabled(true)}>
            {t("details.save")}
          </Button>
        </div>
      </div>
    </div>
  );
};
