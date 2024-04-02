import type { ComponentProps } from "react";
import React from "react";
import type { ContactFormData } from "./Integrations/Detail/ContactInfo";
import { ContactsTableDesktop } from "./ContactsTableDesktop";
import { ContactsTableMobile } from "./ContactsTableMobile";
import { ContactsTableContent } from "./ContactsTableContent";

export type ContactsTableProps = {
  data: ContactFormData;
  onEdit: (id: string) => void;
  onDelete: (id: string, email: string) => void;
  onPreview: (bool: boolean) => void;
  functionalId: string;
  technicalId: string;
} & ComponentProps<"div">;

export const ContactsTable = (props: ContactsTableProps) => {
  return (
    <>
      <ContactsTableDesktop className="max-md:hidden border border-publiq-gray-100">
        <ContactsTableContent {...props} desktop />
      </ContactsTableDesktop>
      <ContactsTableMobile className="md:hidden border border-publiq-gray-100">
        <ContactsTableContent {...props} mobile />
      </ContactsTableMobile>
    </>
  );
};
