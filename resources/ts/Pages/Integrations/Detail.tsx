import React, { ReactNode, useState, useEffect } from "react";
import Layout from "../../layouts/Layout";
import { Page } from "../../Components/Page";
import { Integration } from "./Index";
import { ContactInfo } from "../../Components/Integrations/Detail/ContactInfo";
import { BillingInfo } from "../../Components/Integrations/Detail/BillingInfo";
import { Credentials } from "../../Components/Integrations/Detail/Credentials";
import { IntegrationSettings } from "../../Components/Integrations/Detail/IntegrationSettings";
import { useTranslation } from "react-i18next";
import { router } from "@inertiajs/react";
import { Tabs } from "../../Components/Tabs";
import { DeleteIntegration } from "../../Components/Integrations/Detail/DeleteIntegration";
import { Card } from "../../Components/Card";
import { useIntegrationTypes } from "../../Components/IntegrationTypes";
import { Heading } from "../../Components/Heading";

type Props = { integration: Integration };

const Detail = ({ integration }: Props) => {
  const { t } = useTranslation();

  const [isMobile, setIsMobile] = useState(false);

  const url = new URL(document.location.href);
  const activeTab = url.searchParams.get("tab") ?? "settings";

  const handleResize = () => {
    setIsMobile(window.innerWidth < 768);
  };

  useEffect(() => {
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  const integrationTypes = useIntegrationTypes();
  const integrationType = integrationTypes.find(
    (i) => i.type === integration.type
  );
  const changeTabInUrl = (tab: string) => {
    url.searchParams.set("tab", tab);
    router.get(url.toString());
  };

  return (
    <Page>
      <Card
        title={
          <div className={"flex flex-col"}>
            <Heading className={"font-semibold"} level={2}>
              {integration.name}
            </Heading>
            <small>{integration.type}</small>
          </div>
        }
        icon={integrationType.image}
        border
        headless
      >
        <div className="w-full flex flex-col gap-5">
          <Tabs active={activeTab} onChange={changeTabInUrl}>
            <Tabs.Item
              type="settings"
              label={t("details.integration_settings.title")}
            >
              <IntegrationSettings
                integration={integration}
                {...integration}
                isMobile={isMobile}
              />
            </Tabs.Item>
            <Tabs.Item
              type="credentials"
              label={t("details.credentials.title")}
            >
              <Credentials {...integration} />
            </Tabs.Item>
            <Tabs.Item type="contacts" label={t("details.contact_info.title")}>
              <ContactInfo {...integration} isMobile={isMobile} />
            </Tabs.Item>
            <Tabs.Item
              type="billing"
              label={t("details.billing_info.title.billing")}
            >
              <BillingInfo {...integration} />
            </Tabs.Item>
            <Tabs.Item
              type="delete"
              label={t("details.delete_integration.title")}
            >
              <DeleteIntegration {...integration} />
            </Tabs.Item>
          </Tabs>
        </div>
      </Card>
    </Page>
  );
};

Detail.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Detail;
