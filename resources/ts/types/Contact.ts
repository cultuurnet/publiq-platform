import { ContactType } from "./ContactType";

export type Contact = {
  id: string;
  integrationId: string;
  email: string;
  type: ContactType;
  firstName: string;
  lastName: string;
};
