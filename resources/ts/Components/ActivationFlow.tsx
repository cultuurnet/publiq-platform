import React, { useState } from "react";
import type { IntegrationStatus } from "../types/IntegrationStatus";
import { router } from "@inertiajs/react";
import { ActivationDialog } from "./ActivationDialog";
import type { Subscription } from "../Pages/Integrations/Index";
import type { Values } from "../types/Values";
import type { IntegrationType } from "../types/IntegrationType";
import { ActivationRequest } from "./ActivationRequest";

type Props = {
  status: IntegrationStatus;
  id: string;
  subscription?: Subscription;
  type: Values<typeof IntegrationType>;
  email: string;
};

export const ActivationFlow = ({ id, subscription, type, email }: Props) => {
  const url = new URL(document.location.href);
  const isDialogVisible = url.searchParams.get("isDialogVisible");

  const [isActivationDialogVisible, setIsActivationDialogVisible] =
    useState(!!isDialogVisible);

  return (
    <>
      <ActivationRequest id={id} type={type} />
      <ActivationDialog
        isVisible={isActivationDialogVisible}
        onClose={() => {
          router.get(url.toString(), {
            isDialogVisible: undefined,
          });
          setIsActivationDialogVisible(false);
        }}
        id={id}
        subscription={subscription}
        type={type}
        email={email}
      />
    </>
  );
};
