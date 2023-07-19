import React, { useState } from "react";
import { Heading } from "../../Heading";
import { useTranslation } from "react-i18next";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { Button } from "../../Button";
import { FormDropdown } from "../../FormDropdown";
import { ButtonIcon } from "../../ButtonIcon";
import { faPencil } from "@fortawesome/free-solid-svg-icons";

type Props = {
  isMobile?: boolean;
};

export const IntegrationSettings = ({ isMobile }: Props) => {
  const { t } = useTranslation();

  const [isDisabled, setIsDisabled] = useState(true);

  return (
    <FormDropdown
      title={t("details.integration_settings.title")}
      actions={
        <ButtonIcon
          icon={faPencil}
          className="text-icon-gray"
          onClick={() => setIsDisabled((prev) => !prev)}
        />
      }
    >
      <Heading className="font-semibold" level={3}>
        {t("details.integration_settings.login")}
      </Heading>
      <FormElement
        label={`${t("details.integration_settings.test")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="loginTest"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      />
      <FormElement
        label={`${t("details.integration_settings.production")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="loginProduction"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      />
      <Heading className="font-semibold" level={3}>
        {t("details.integration_settings.callback")}
      </Heading>
      <FormElement
        label={`${t("details.integration_settings.test")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="callbackTest"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      />
      <FormElement
        label={`${t("details.integration_settings.production")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="callbackProduction"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      />
      <Heading className="font-semibold" level={3}>
        {t("details.integration_settings.logout")}
      </Heading>
      <FormElement
        label={`${t("details.integration_settings.test")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="logoutTest"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      />
      <FormElement
        label={`${t("details.integration_settings.production")}`}
        labelPosition={isMobile ? "top" : "left"}
        component={
          <Input
            type="text"
            name="logoutProduction"
            defaultValue=""
            className="md:min-w-[32rem]"
            disabled={isDisabled}
          />
        }
      />
      {!isDisabled && (
        <div className="flex flex-col items-start md:pl-[10.5rem]">
          <Button onClick={() => setIsDisabled(true)}>
            {t("details.save")}
          </Button>
        </div>
      )}
    </FormDropdown>
  );
};
