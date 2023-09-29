import React, { ReactNode, useState, useEffect } from "react";
import Layout from "../../Components/Layout";
import { Page } from "../../Components/Page";
import { Heading } from "../../Components/Heading";
import { Integration } from "./Index";
import { BasicInfo } from "../../Components/Integrations/Detail/BasicInfo";
import { ContactInfo } from "../../Components/Integrations/Detail/ContactInfo";
import { BillingInfo } from "../../Components/Integrations/Detail/BillingInfo";
import { IntegrationInfo } from "../../Components/Integrations/Detail/IntegrationInfo";
import { IntegrationSettings } from "../../Components/Integrations/Detail/IntegrationSettings";
import { useTranslation } from "react-i18next";
import { router } from "@inertiajs/react";
import { Tabs } from "../../Components/Tabs";
import { MenageIntegration } from "../../Components/Integrations/Detail/MenageIntegration";

type Props = { integration: Integration };

const Detail = ({ integration }: Props) => {
  const { t } = useTranslation();

  const [isMobile, setIsMobile] = useState(false);

  const url = new URL(document.location.href);
  const activeTab = url.searchParams.get("tab") ?? "basic_info";

  const handleResize = () => {
    setIsMobile(window.innerWidth < 768);
  };

  useEffect(() => {
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  const changeTabInUrl = (tab: string) => {
    url.searchParams.set("tab", tab);
    router.get(url.toString());
  };

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

        <Tabs active={activeTab} onChange={changeTabInUrl}>
          <Tabs.Item type="basic_info" label={t("details.basic_info.title")}>
            <BasicInfo integration={integration} isMobile={isMobile} />
          </Tabs.Item>
          <Tabs.Item
            type="integration_info"
            label={t("details.integration_info.title")}
          >
            <IntegrationInfo {...integration} />
          </Tabs.Item>
          <Tabs.Item
            type="integration_settings"
            label={t("details.integration_settings.title")}
          >
            <IntegrationSettings {...integration} isMobile={isMobile} />
          </Tabs.Item>
          <Tabs.Item
            type="contact_info"
            label={t("details.contact_info.title")}
          >
            <ContactInfo {...integration} isMobile={isMobile} />
          </Tabs.Item>
          <Tabs.Item
            type="billing_info"
            label={t("details.billing_info.title")}
          >
            <BillingInfo {...integration} />
          </Tabs.Item>
          <Tabs.Item
            type="menage_account"
            label={t("details.menage_account.title")}
          >
            <MenageIntegration {...integration} />
          </Tabs.Item>
        </Tabs>
      </div>
    </Page>
  );
};

Detail.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Detail;
