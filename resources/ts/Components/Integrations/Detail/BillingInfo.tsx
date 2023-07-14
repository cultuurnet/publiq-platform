import React, { useState } from "react";
import { Heading } from "../../Heading";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { Button } from "../../Button";
import { FormDropdown } from "../../FormDropdown";
import { useTranslation } from "react-i18next";

export const BillingInfo = () => {
  const { t } = useTranslation();
  const [isDisabled, setIsDisabled] = useState(true);

  return (
    <FormDropdown
      title={t("details.billing_info.title")}
      disabled={isDisabled}
      onChangeDisabled={(newDisabled) => {
        setIsDisabled(newDisabled);
      }}
    >
      <div className="flex flex-col gap-5">
        <div className="flex max-sm:flex-col md:items-center gap-2">
          <Heading level={5} className="font-semibold w50">
            {t("details.billing_info.subscription")}
          </Heading>
          <p>Plus $250</p>
        </div>
        <div className="grid md:w-[50%] ">
          <FormElement
            label={`${t("details.billing_info.name")}`}
            component={
              <Input
                type="text"
                name="name"
                defaultValue={`${t("details.billing_info.name")}`}
                disabled={isDisabled}
              />
            }
          />
        </div>
        <div className="grid grid-cols-3 gap-3 max-md:grid-cols-1">
          <FormElement
            label={`${t("details.billing_info.address.street")}`}
            component={
              <Input
                type="text"
                name="street"
                defaultValue={`${t("details.billing_info.address.street")}`}
                disabled={isDisabled}
              />
            }
          />
          <FormElement
            label={`${t("details.billing_info.address.postcode")}`}
            component={
              <Input
                type="text"
                name="postcode"
                defaultValue={`${t("details.billing_info.address.postcode")}`}
                disabled={isDisabled}
              />
            }
          />

          <FormElement
            label={`${t("details.billing_info.address.city")}`}
            component={
              <Input
                type="text"
                name="firstNameTechnicalContact"
                defaultValue={`${t("details.billing_info.address.city")}`}
                disabled={isDisabled}
              />
            }
          />
        </div>
        <div className="grid md:w-[50%]">
          <FormElement
            label={`${t("details.billing_info.vat")}`}
            component={
              <Input
                type="text"
                name="firstNameTechnicalContact"
                defaultValue={`${t("details.billing_info.vat")}`}
                disabled={isDisabled}
              />
            }
          />
        </div>
        <div className="flex flex-col gap-2 items-center">
          <Button onClick={() => setIsDisabled(true)}>
            {t("details.save")}
          </Button>
        </div>
      </div>
    </FormDropdown>
  );
};
