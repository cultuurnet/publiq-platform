import React, { ComponentProps } from "react";
import { IntegrationStatus } from "../types/IntegrationStatus";

type Props = ComponentProps<"div"> & {
  status: IntegrationStatus;
};

export const Status = ({ status }: Props) => {
  return <div></div>;
};
