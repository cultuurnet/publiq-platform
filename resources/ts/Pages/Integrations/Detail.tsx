import type { ReactNode } from "react";
import React, { useEffect, useState } from "react";
import Layout from "../../layouts/Layout";
import { Page } from "../../Components/Page";
import type { Integration } from "./Index";
import { ContactInfo } from "../../Components/Integrations/Detail/ContactInfo";
import { BillingInfo } from "../../Components/Integrations/Detail/BillingInfo";
import { Credentials } from "../../Components/Integrations/Detail/Credentials";
import { IntegrationSettings } from "../../Components/Integrations/Detail/IntegrationSettings";
import { useTranslation } from "react-i18next";
import { router } from "@inertiajs/react";
import { Tabs } from "../../Components/Tabs";
import { DeleteIntegration } from "../../Components/Integrations/Detail/DeleteIntegration";
import { Card } from "../../Components/Card";
import {
  integrationIconClasses,
  integrationTypesIcons,
} from "../../Components/IntegrationTypes";
import { Heading } from "../../Components/Heading";
import { IntegrationType } from "../../types/IntegrationType";

type Props = { integration: Integration; email: string };

const Detail = ({ integration, email }: Props) => {
  const { t } = useTranslation();

  const [isMobile, setIsMobile] = useState(false);

  const changeTabInUrl = (tab: string) => {
    router.get(url, {
      tab,
    });
  };

  const url = new URL(document.location.href);
  const activeTab = url.searchParams.get("tab") ?? "credentials";

  const handleResize = () => {
    setIsMobile(window.innerWidth < 768);
  };

  useEffect(() => {
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  const [isFormDirty, setIsFormDirty] = useState(false);
  const [isKeepChangesDialogVisible, setIsKeepChangesDialogVisible] =
    useState(false);
  const [originalVisitingUrl, setOriginalVisitingUrl] = useState<string>();

  const handleChangeIsFormDirty = (newValue: boolean) =>
    setIsFormDirty(newValue);

  const Icon = integrationTypesIcons[integration.type];

  const handleConfirmLeaveTab = () => {
    if (!originalVisitingUrl) return;

    router.get(originalVisitingUrl);
  };

  const handleCancelLeaveTab = () => {
    setIsKeepChangesDialogVisible(false);
    setOriginalVisitingUrl("");
  };

  useEffect(() => {
    const cleanUp = router.on("before", (e) => {
      const nextVisit = e.detail.visit;
      const newUrl = new URL(nextVisit.url);

      if (nextVisit.url.toString() === originalVisitingUrl) {
        return;
      }

      if (nextVisit.method !== "get") {
        return;
      }

      if (activeTab !== "settings") {
        return;
      }

      const hasPageChange =
        (newUrl.pathname === location.pathname &&
          activeTab !== newUrl.searchParams.get("tab")) ||
        newUrl.pathname !== location.pathname;

      if (!hasPageChange) {
        return;
      }

      if (!isFormDirty) {
        return;
      }

      setOriginalVisitingUrl(nextVisit.url.toString());
      e.preventDefault();
      setIsKeepChangesDialogVisible(true);
    });

    return () => cleanUp();
  }, [isFormDirty, originalVisitingUrl, activeTab]);

  return (
    <Page>
      <Card
        title={
          <div className="flex flex-col">
            <Heading className="font-semibold" level={2}>
              {integration.name}
            </Heading>
            <small>{t(`integrations.products.${integration.type}`)}</small>
          </div>
        }
        icon={Icon && <Icon className={integrationIconClasses} />}
        border
        headless
      >
        <div className="w-full flex flex-col gap-5">
          <Tabs active={activeTab} onChange={changeTabInUrl}>
            <Tabs.Item
              type="credentials"
              label={
                integration.type === IntegrationType.Widgets
                  ? t("details.credentials.widgets")
                  : t("details.credentials.title")
              }
            >
              <Credentials {...integration} email={email} />
            </Tabs.Item>
            <Tabs.Item
              type="settings"
              label={t("details.integration_settings.title")}
            >
              <IntegrationSettings
                {...integration}
                isMobile={isMobile}
                onChangeIsFormDirty={handleChangeIsFormDirty}
                isKeepChangesDialogVisible={isKeepChangesDialogVisible}
                onConfirmLeaveTab={handleConfirmLeaveTab}
                onCancelLeaveTab={handleCancelLeaveTab}
              />
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
