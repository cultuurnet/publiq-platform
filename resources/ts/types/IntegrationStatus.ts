import type { Values } from "./Values";

export const IntegrationStatus = {
  Draft: "draft",
  Active: "active",
  Blocked: "blocked",
  Deleted: "deleted",
  PendingApprovalIntegration: "pending_approval_integration",
  PendingApprovalPayment: "pending_approval_payment",
} as const;

export type IntegrationStatus = Values<typeof IntegrationStatus>;
