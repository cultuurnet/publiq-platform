import React, { ReactNode, useState, useEffect } from "react";
import Layout from "../../Components/Layout";
import { Page } from "../../Components/Page";
import { Heading } from "../../Components/Heading";
import { faTrash } from "@fortawesome/free-solid-svg-icons";
import { Integration } from "./Index";
import { BasicInfo } from "../../Components/Integrations/Detail/BasicInfo";
import { ContactInfo } from "../../Components/Integrations/Detail/ContactInfo";
import { BillingInfo } from "../../Components/Integrations/Detail/BillingInfo";
import { IntegrationInfo } from "../../Components/Integrations/Detail/IntegrationInfo";
import { IntegrationSettings } from "../../Components/Integrations/Detail/IntegrationSettings";
import { ButtonSecondary } from "../../Components/ButtonSecondary";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { useTranslation } from "react-i18next";

type Props = { integration: Integration };

const Detail = ({ integration }: Props) => {
  const { t } = useTranslation();

  const [isMobile, setIsMobile] = useState(false);

  const handleResize = () => {
    setIsMobile(window.innerWidth < 768);
  };

  useEffect(() => {
    window.addEventListener("resize", handleResize);
  });

  return (
    <Page>
      <div className="w-full flex flex-col gap-5">
        <div className="flex justify-between gap-5">
          <div className="flex gap-3 items-center align-middle">
            <Heading className="font-semibold" level={2}>
              {integration.name}
            </Heading>
            <span className="bg-publiq-blue-dark text-white text-xs font-medium  mr-2 px-2.5 py-0.5 rounded">
              {integration.type}
            </span>
          </div>
        </div>

        <BasicInfo integration={integration} isMobile={isMobile} />
        <IntegrationInfo {...integration} />
        <IntegrationSettings isMobile={isMobile} />
        <ContactInfo {...integration} />
        <BillingInfo />
        <ButtonSecondary className="self-center">
          {t("details.delete")}
          <FontAwesomeIcon className="pl-1" icon={faTrash} />
        </ButtonSecondary>
      </div>
    </Page>
  );
};

Detail.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Detail;
