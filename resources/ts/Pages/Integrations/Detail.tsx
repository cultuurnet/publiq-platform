import React, { useEffect, useState } from "react";
import { Page } from "../../Components/Page";
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
import { useGetPricingPlans } from "../../hooks/useGetPricingPlans";
import type { Subscription } from "../../types/Subscription";
import { PricingPlanProvider } from "../../Context/PricingPlan";
import { useIsMobile } from "../../hooks/useIsMobile";
import { CouponInfoProvider } from "../../Context/CouponInfo";
import type { Integration } from "../../types/Integration";
import { OrganizersInfo } from "../../Components/Integrations/Detail/OrganizersInfo";
import type { Organizer } from "../../types/Organizer";

type Props = {
  integration: Integration;
  email: string;
  subscriptions: Subscription[];
  oldCredentialsExpirationDate: string;
  errors: Record<string, string | undefined>;
  organizers: Organizer[];
};

const Detail = ({
  integration,
  email,
  subscriptions,
  oldCredentialsExpirationDate,
  organizers,
  errors,
}: Props) => {
  const { t } = useTranslation();
  const isMobile = useIsMobile();

  const duplicateContactErrorMessage = errors["duplicate_contact"];

  const pricingPlans = useGetPricingPlans(integration.type, subscriptions);

  // Should always be defined
  const pricingPlan = pricingPlans.find(
    (pricingPlan) => pricingPlan.id === integration.subscription.id
  )!;

  const changeTabInUrl = (tab: string) => {
    router.get(url, {
      tab,
    });
  };

  const url = new URL(document.location.href);
  const activeTab = url.searchParams.get("tab") ?? "credentials";

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

  const isUitpasIntegration = integration.type === IntegrationType.UiTPAS;

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
        <div className="w-full flex flex-col gap-5 pb-3">
          <PricingPlanProvider pricingPlan={pricingPlan}>
            <CouponInfoProvider couponInfo={integration.coupon}>
              <Tabs active={activeTab} onChange={changeTabInUrl}>
                <Tabs.Item
                  type="credentials"
                  label={
                    integration.type === IntegrationType.Widgets
                      ? t("details.credentials.widgets")
                      : t("details.credentials.title")
                  }
                >
                  <Credentials
                    {...integration}
                    email={email}
                    oldCredentialsExpirationDate={oldCredentialsExpirationDate}
                  />
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
                <Tabs.Item
                  type="contacts"
                  label={t("details.contact_info.title")}
                >
                  <ContactInfo
                    {...integration}
                    isMobile={isMobile}
                    duplicateContactErrorMessage={duplicateContactErrorMessage}
                  />
                </Tabs.Item>
                {isUitpasIntegration && (
                  <Tabs.Item
                    type="organisations"
                    label={t("details.organizers_info.title")}
                  >
                    <OrganizersInfo {...integration} organizers={organizers} />
                  </Tabs.Item>
                )}
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
            </CouponInfoProvider>
          </PricingPlanProvider>
        </div>
      </Card>
    </Page>
  );
};

export default Detail;
