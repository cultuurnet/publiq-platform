import React, { useMemo } from "react";
import type { Integration } from "../../../Pages/Integrations/Index";
import { CredentialsUitidv1Consumers } from "./CredentialsUitidv1Consumers";
import { CredentialsAuth0Clients } from "./CredentialsAuth0Clients";

type Props = Integration & {
  email: string;
};

export const Credentials = ({
  id,
  status,
  email,
  subscription,
  type,
  uiTiDv1Consumers,
  auth0Clients,
}: Props) => {
  const uiTiDv1TestConsumer = useMemo(
    () => uiTiDv1Consumers.find((consumer) => consumer.environment === "test"),
    [uiTiDv1Consumers]
  );

  return (
    <>
      {uiTiDv1TestConsumer && (
        <div className="flex w-full max-lg:flex-col gap-6 border-b pb-10 border-gray-300">
          <CredentialsUitidv1Consumers
            uiTiDv1Consumers={uiTiDv1Consumers}
            auth0Clients={auth0Clients}
            email={email}
            status={status}
            id={id}
            type={type}
            subscription={subscription}
          />
        </div>
      )}
      <div className="flex w-full max-lg:flex-col gap-6">
        <CredentialsAuth0Clients
          uiTiDv1Consumers={uiTiDv1Consumers}
          auth0Clients={auth0Clients}
          email={email}
          status={status}
          id={id}
          type={type}
          subscription={subscription}
        />
      </div>
    </>
  );
};
