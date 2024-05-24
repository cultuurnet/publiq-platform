import React, { useContext } from "react";
import { Heading } from "../../Heading";
import { FormElement } from "../../FormElement";
import { Input } from "../../Input";
import { ButtonPrimary } from "../../ButtonPrimary";
import { useTranslation } from "react-i18next";
import { useForm } from "@inertiajs/react";
import { Alert } from "../../Alert";
import { IntegrationType } from "../../../types/IntegrationType";
import { IntegrationStatus } from "../../../types/IntegrationStatus";
import { PricingPlanContext } from "../../../Context/PricingPlan";
import { formatCurrency } from "../../../utils/formatCurrency";
import { formatPricing } from "../../../utils/formatPricing";
import type { Integration } from "../../../types/Integration";

type Props = Integration;

export const OrganizersInfo = ({
  id,
  organization,
  subscription,
  coupon,
  status,
}: Props) => {
  const { t } = useTranslation();

  console.log(organization);
  return (
    <>
      <Heading level={4} className="font-semibold">
        {t("details.organizers_info.title")}
      </Heading>
      <p>
        Hieronder vind je een overzicht van de UiTdatabank organisaties waarvoor
        je acties kan uitvoeren in de UiTPAS API.
      </p>
    </>
  );
};
